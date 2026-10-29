<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Perhitungan MSA Gage R&R (metode Average & Range) — mengikuti template Excel:
 *  EV  = R̿ × K1                    AV = √[(X̄diff × K2)² − EV² / (n·r)]
 *  GRR = √(EV² + AV²)               PV = Rp × K3
 *  TV  = √(GRR² + PV²)              ndc = 1.41 × PV / GRR
 *  %X  = 100 × X / TV
 */
class GageRRCalculator
{
    public const K1 = [2 => 0.8862, 3 => 0.5908];
    public const K2 = [2 => 0.7071, 3 => 0.5231];
    public const K3 = [2 => 0.7071, 3 => 0.5231, 4 => 0.4467, 5 => 0.4030, 6 => 0.3742, 7 => 0.3534, 8 => 0.3375, 9 => 0.3249, 10 => 0.3146];
    public const D4 = [2 => 3.27, 3 => 2.58];
    public const APPRAISERS = ['A', 'B', 'C'];

    /**
     * @param array $general   partNoName, characteristics, specifications, gageName, gageNo, gageType, date, performedBy, appraiserA, appraiserB, appraiserC
     * @param array $settings  appraisers (2|3), trials (2|3), parts (2..10)
     * @param array $meas      ['A' => [[p1..p10], [..], [..]], 'B' => ..., 'C' => ...]
     * @return array ['v' => array<string,string> (siap cetak), 'raw' => array, 'complete' => bool]
     */
    public static function compute(array $general, array $settings, array $meas): array
    {
        $nApp   = max(2, min(3, (int) ($settings['appraisers'] ?? 3)));
        $nTrial = max(2, min(3, (int) ($settings['trials'] ?? 3)));
        $nPart  = max(2, min(10, (int) ($settings['parts'] ?? 10)));
        $apps   = array_slice(self::APPRAISERS, 0, $nApp);

        $num = static function ($x) {
            if ($x === null || $x === '' || !is_numeric($x)) return null;
            return (float) $x;
        };

        $v = [];
        $raw = ['trial' => [], 'avg' => [], 'rng' => [], 'xa' => [], 'ra' => []];
        $complete = true;

        // ---- data sheet: trial, average, range per appraiser/part
        foreach ($apps as $a) {
            for ($p = 0; $p < $nPart; $p++) {
                $vals = [];
                for ($t = 0; $t < $nTrial; $t++) {
                    $x = $num($meas[$a][$t][$p] ?? null);
                    $raw['trial'][$a][$t][$p] = $x;
                    $v["t_{$a}_" . ($t + 1) . '_' . ($p + 1)] = $x === null ? '' : self::fmtInput($x);
                    if ($x === null) $complete = false; else $vals[] = $x;
                }
                if (count($vals) === $nTrial) {
                    $raw['avg'][$a][$p] = array_sum($vals) / $nTrial;
                    $raw['rng'][$a][$p] = max($vals) - min($vals);
                    $v["avg_{$a}_" . ($p + 1)] = self::fmt($raw['avg'][$a][$p], 2);
                    $v["rng_{$a}_" . ($p + 1)] = self::fmt($raw['rng'][$a][$p], 2);
                }
            }
            if (count($raw['avg'][$a] ?? []) === $nPart) {
                $raw['xa'][$a] = array_sum($raw['avg'][$a]) / $nPart;
                $raw['ra'][$a] = array_sum($raw['rng'][$a]) / $nPart;
                $l = strtolower($a);
                $v["x{$l}"] = self::fmt($raw['xa'][$a], 3);
                $v["r{$l}"] = self::fmt($raw['ra'][$a], 3);
            }
        }

        // ---- part average
        $partAvg = [];
        for ($p = 0; $p < $nPart; $p++) {
            $col = [];
            foreach ($apps as $a) if (isset($raw['avg'][$a][$p])) $col[] = $raw['avg'][$a][$p];
            if (count($col) === $nApp) {
                $partAvg[$p] = array_sum($col) / $nApp;
                $v['pavg_' . ($p + 1)] = self::fmt($partAvg[$p], 2);
            }
        }

        $res = ['complete' => $complete && count($partAvg) === $nPart];

        if ($res['complete']) {
            $xbar  = array_sum($partAvg) / $nPart;
            $rp    = max($partAvg) - min($partAvg);
            $rbb   = array_sum($raw['ra']) / $nApp;
            $xdiff = max($raw['xa']) - min($raw['xa']);
            $ucl   = $rbb * self::D4[$nTrial];

            $ev  = $rbb * self::K1[$nTrial];
            $av2 = pow($xdiff * self::K2[$nApp], 2) - pow($ev, 2) / ($nPart * $nTrial);
            $av  = $av2 > 0 ? sqrt($av2) : 0.0;
            $grr = sqrt($ev ** 2 + $av ** 2);
            $pv  = $rp * self::K3[$nPart];
            $tv  = sqrt($grr ** 2 + $pv ** 2);

            $res += compact('xbar', 'rp', 'rbb', 'xdiff', 'ucl', 'ev', 'av', 'grr', 'pv', 'tv');
            $res['pev']  = $tv > 0 ? 100 * $ev / $tv : 0;
            $res['pav']  = $tv > 0 ? 100 * $av / $tv : 0;
            $res['pgrr'] = $tv > 0 ? 100 * $grr / $tv : 0;
            $res['ppv']  = $tv > 0 ? 100 * $pv / $tv : 0;
            $res['ndc']  = $grr > 0 ? 1.41 * $pv / $grr : 0;

            // halaman 1
            $v['p1_xbar']  = self::fmt($xbar, 3);
            $v['p1_rp']    = self::fmt($rp, 3);
            $v['p1_rbb']   = self::fmt($rbb, 3);
            $v['p1_xdiff'] = self::fmt($xdiff, 3);
            // halaman 2
            $v['p2_rbb']   = self::fmt($rbb, 4);
            $v['p2_xdiff'] = self::fmtGeneral($xdiff);
            $v['p2_rp']    = self::fmtGeneral($rp);
            foreach (['ev', 'av', 'grr', 'pv', 'tv'] as $k) $v[$k] = self::fmt($res[$k], 3);
            foreach (['pev', 'pav', 'pgrr', 'ppv', 'ndc'] as $k) $v[$k] = self::fmt($res[$k], 2);
        }

        // ---- header (halaman 2) + ukuran font agar muat di kolomnya
        $date = '';
        if (!empty($general['date'])) {
            try { $date = Carbon::parse($general['date'])->locale('en')->format('M, d Y'); } catch (\Throwable $e) { $date = (string) $general['date']; }
        }
        $hdr = [
            'h_part'   => [$general['partNoName'] ?? '',     90],
            'h_char'   => [$general['characteristics'] ?? '', 90],
            'h_spec'   => [$general['specifications'] ?? '',  90],
            'h_gname'  => [$general['gageName'] ?? '',        90],
            'h_gno'    => [$general['gageNo'] ?? '',          90],
            'h_gtype'  => [$general['gageType'] ?? '',        90],
            'h_date'   => [$date,                            110],
            'h_by'     => [$general['performedBy'] ?? '',    110],
        ];
        foreach ($hdr as $k => [$text, $w]) {
            $v[$k] = (string) $text;
            $v[$k . '_fs'] = self::fitFont((string) $text, $w);
        }
        
        // ---- appraiser names (untuk ditampilkan di PDF)
        $v['appraiserA'] = $general['appraiserA'] ?? '';
        $v['appraiserB'] = $general['appraiserB'] ?? '';
        $v['appraiserC'] = $general['appraiserC'] ?? '';

        return ['v' => $v, 'raw' => $raw, 'result' => $res, 'complete' => $res['complete']];
    }

    /** Status AIAG: <10% baik, 10–30% bersyarat, >30% tidak dapat diterima */
    public static function status(float $pctGrr): string
    {
        return $pctGrr < 10 ? 'Diterima' : ($pctGrr <= 30 ? 'Bersyarat' : 'Tidak diterima');
    }

    public static function fmt(float $x, int $dec): string
    {
        $x = round($x, $dec);
        if ($x == 0) $x = 0.0; // hindari "-0,00"
        return number_format($x, $dec, ',', '');
    }

    /** Format "General" ala Excel (maks. 9 desimal, nol di belakang dibuang) */
    public static function fmtGeneral(float $x): string
    {
        $s = rtrim(rtrim(number_format(round($x, 9), 9, ',', ''), '0'), ',');
        return $s === '' || $s === '-0' ? '0' : $s;
    }

    /** Nilai pengukuran: minimal 2 desimal, tampil sampai 4 desimal bila diinput lebih presisi */
    public static function fmtInput(float $x): string
    {
        $s = number_format(round($x, 4), 4, ',', '');
        $s = rtrim($s, '0');
        $dec = strlen(substr(strrchr($s, ','), 1));
        return $dec < 2 ? $s . str_repeat('0', 2 - $dec) : $s;
    }

    /** Perkecil font (10 → 6 pt) bila teks melebihi lebar kolom (pt) */
    public static function fitFont(string $text, float $maxWidth): float
    {
        $len = max(1, mb_strlen($text));
        foreach ([10, 9, 8, 7, 6] as $fs) {
            if ($len * $fs * 0.52 <= $maxWidth) return $fs;
        }
        return 6;
    }
}
