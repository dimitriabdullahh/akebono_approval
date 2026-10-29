<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gage Repeatability and Reproducibility</title>
    <style>
        @page { margin: 0; size: letter portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; color: #000; }
        .page { position: relative; width: 612pt; height: 791pt; page-break-after: always; overflow: hidden; }
        .page:last-child { page-break-after: auto; }
        .r, .t, .v, .rad { position: absolute; }
        .r { background: #000; }
        .r.g { background: #ccffcc; }
        .t { white-space: nowrap; font-size: 10pt; line-height: 10pt; }
        .v { white-space: nowrap; font-size: 10pt; line-height: 10pt; height: 10pt; }
        .rad { left: 0; top: 0; width: 612pt; height: 792pt; }
        .b { font-weight: bold; }
        .f120 { font-size: 12pt; line-height: 12pt; }
        .f100 { font-size: 10pt; line-height: 10pt; }
        .f110 { font-size: 11pt; line-height: 11pt; }
        .f66 { font-size: 6.6pt; line-height: 6.6pt; }
        .f60 { font-size: 6pt; line-height: 6pt; }
        .f80 { font-size: 8pt; line-height: 8pt; }
    </style>
</head>
<body>
        <?php
            // ===================================================================
            // PERHITUNGAN (setara rumus Excel pada form acuan)
            // Input  : $v['t_{A|B|C}_{trial 1-3}_{part 1-10}'] = hasil ukur (angka / "7,5")
            // Output : $o[...] = teks yang sudah diformat, dicetak di bawah
            // Jika belum ada data -> tampil seperti acuan (#DIV/0! / 0.00 / 0.000)
            // ===================================================================
            $num = function ($x) {
                if (is_string($x)) { $x = trim(str_replace(',', '.', $x)); }
                return ($x !== null && $x !== '' && is_numeric($x)) ? (float) $x : null;
            };
            $fmt  = fn($x, $d) => ($x === null || is_nan($x) || is_infinite($x)) ? '#DIV/0!' : number_format($x, $d, '.', '');
            // ukuran font otomatis agar teks header muat di kolomnya (bila $v['h_*_fs'] tidak dikirim)
            $fit  = fn($text, $w) => max(5, min(10, round($w / max(1, mb_strlen((string) $text)) / 0.52, 1)));
            $mean = fn(array $a) => count($a) ? array_sum($a) / count($a) : null;

            $o = []; $X = []; $R = []; $partAvg = []; $r = 0;
            foreach (['A', 'B', 'C'] as $a) {
                $xs = []; $rs = [];
                for ($p = 1; $p <= 10; $p++) {
                    $vals = [];
                    for ($t = 1; $t <= 3; $t++) {
                        $n = $num($v["t_{$a}_{$t}_{$p}"] ?? null);
                        if ($n !== null) { $vals[] = $n; }
                    }
                    if (count($vals)) {
                        $m  = array_sum($vals) / count($vals);
                        $rg = max($vals) - min($vals);
                        $xs[] = $m; $rs[] = $rg;
                        $partAvg[$p][] = $m;
                        $r = max($r, count($vals));
                        $o["avg_{$a}_{$p}"] = $fmt($m, 2);
                        $o["rng_{$a}_{$p}"] = $fmt($rg, 2);
                    } else {
                        $o["avg_{$a}_{$p}"] = '#DIV/0!';
                        $o["rng_{$a}_{$p}"] = '0.00';
                    }
                }
                if (count($xs)) { $X[$a] = $mean($xs); $R[$a] = $mean($rs); }
                $k = strtolower($a);
                $o["x{$k}"] = $fmt($X[$a] ?? null, 3);
                $o["r{$k}"] = $fmt($R[$a] ?? 0, 3);
            }
            $pAvg = [];
            for ($p = 1; $p <= 10; $p++) {
                if (isset($partAvg[$p])) { $pAvg[$p] = $mean($partAvg[$p]); }
                $o["pavg_{$p}"] = $fmt($pAvg[$p] ?? null, 2);
            }

            $n    = count($pAvg);              // n = jumlah part
            $k    = count($X);                 // jumlah appraiser
            $Rbar = count($R) ? $mean($R) : null;
            $Xbar = count($X) ? $mean($X) : null;
            $Rp   = $n ? max($pAvg) - min($pAvg) : null;
            $Xdif = $k ? max($X) - min($X) : null;

            $K1 = [2 => 0.8862, 3 => 0.5908][$r] ?? null;
            $K2 = [2 => 0.7071, 3 => 0.5231][$k] ?? null;
            $K3 = [2 => 0.7071, 3 => 0.5231, 4 => 0.4467, 5 => 0.4030, 6 => 0.3742, 7 => 0.3534, 8 => 0.3375, 9 => 0.3249, 10 => 0.3146][$n] ?? null;
            $D4 = [2 => 3.27, 3 => 2.58][$r] ?? null;

            $EV  = $n == 0 ? 0.0 : (($Rbar !== null && $K1 !== null) ? $Rbar * $K1 : null);
            $AV  = ($Xdif !== null && $K2 !== null && $EV !== null && $n * $r > 0)
                    ? sqrt(max(0, pow($Xdif * $K2, 2) - pow($EV, 2) / ($n * $r))) : null;
            $GRR = ($EV !== null && $AV !== null) ? sqrt($EV * $EV + $AV * $AV) : null;
            $PV  = ($Rp !== null && $K3 !== null) ? $Rp * $K3 : null;
            $TV  = ($GRR !== null && $PV !== null) ? sqrt($GRR * $GRR + $PV * $PV) : null;
            $pct = fn($x) => ($x !== null && $TV) ? 100 * $x / $TV : null;

            // Halaman 1 - kolom kanan
            $o['p1_xbar']  = $fmt($Xbar, 3);
            $o['p1_rp']    = $fmt($Rp, 3);
            $o['p1_rbb']   = $fmt($Rbar ?? 0, 3);
            $o['p1_xdiff'] = $fmt($Xdif, 3);
            // Halaman 1 - isian baris rumus (kosong bila belum ada data, seperti acuan)
            $o['f_ra'] = isset($R['A']) ? $fmt($R['A'], 3) : '';
            $o['f_rb'] = isset($R['B']) ? $fmt($R['B'], 3) : '';
            $o['f_rc'] = isset($R['C']) ? $fmt($R['C'], 3) : '';
            $o['f_nap'] = $k ? (string) $k : '';
            $o['f_rbar_sum'] = $k ? $fmt($Rbar, 3) : '';
            $o['f_max'] = $k ? $fmt(max($X), 3) : '';
            $o['f_min'] = $k ? $fmt(min($X), 3) : '';
            $o['f_xdiff_res'] = $k ? $fmt($Xdif, 3) : '';
            $o['f_rbar'] = $k ? $fmt($Rbar, 3) : '';
            $o['f_d4']   = $D4 !== null ? number_format($D4, 2, '.', '') : '';
            $o['f_ucl']  = ($k && $D4 !== null) ? $fmt($Rbar * $D4, 3) : '';
            // Halaman 2
            $o['p2_rbb']   = $fmt($Rbar ?? 0, 4);
            $o['p2_xdiff'] = $fmt($Xdif, 3);
            $o['p2_rp']    = $fmt($Rp, 3);
            $o['ev'] = $fmt($EV, 3);  $o['av'] = $fmt($AV, 3);  $o['grr'] = $fmt($GRR, 3);
            $o['pv'] = $fmt($PV, 3);  $o['tv'] = $fmt($TV, 3);
            $o['pev']  = $fmt($pct($EV), 2);  $o['pav'] = $fmt($pct($AV), 2);
            $o['pgrr'] = $fmt($pct($GRR), 2); $o['ppv'] = $fmt($pct($PV), 2);
            $o['ndc']  = $fmt(($PV !== null && $GRR) ? 1.41 * $PV / $GRR : null, 2);
        ?>



    
    <div class="page">
        <div class="r g" style="left:95.28pt;top:193.68pt;width:370.92pt;height:52.32pt"></div>
        <div class="r g" style="left:95.28pt;top:284.76pt;width:370.92pt;height:52.32pt"></div>
        <div class="r g" style="left:95.28pt;top:375.84pt;width:370.92pt;height:52.32pt"></div>
        <div class="r" style="left:29.4pt;top:130.68pt;width:0.96pt;height:529.92pt"></div>
        <div class="r" style="left:30.36pt;top:130.68pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:581.52pt;top:131.64pt;width:0.96pt;height:528.96pt"></div>
        <div class="r" style="left:30.36pt;top:156.96pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:94.8pt;top:157.92pt;width:0.96pt;height:348.36pt"></div>
        <div class="r" style="left:465.6pt;top:157.92pt;width:0.96pt;height:420.72pt"></div>
        <div class="r" style="left:95.76pt;top:175.08pt;width:370.8pt;height:0.96pt"></div>
        <div class="r" style="left:131.88pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:168.96pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:206.04pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:243.12pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:280.2pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:317.28pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:354.36pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:391.44pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:428.52pt;top:176.04pt;width:0.96pt;height:330.24pt"></div>
        <div class="r" style="left:30.36pt;top:193.2pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:210.6pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:228pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:245.4pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:475.08pt;top:248.52pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:264.84pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:474.24pt;top:267.96pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:284.28pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:301.68pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:319.08pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:336.48pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:475.08pt;top:339.6pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:355.92pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:474.24pt;top:359.04pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:375.36pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:392.76pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:410.16pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:427.56pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:475.08pt;top:430.68pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:447pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:474.24pt;top:450.12pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:466.44pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:474.24pt;top:468.12pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:474.24pt;top:471pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:466.56pt;top:485.88pt;width:115.92pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:505.32pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:474.24pt;top:507.72pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:474.24pt;top:510.6pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:47.52pt;top:511.44pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:112.92pt;top:511.44pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:179.16pt;top:511.44pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:529.44pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:191.88pt;top:533.28pt;width:7.8pt;height:0.84pt"></div>
        <div class="r" style="left:475.08pt;top:533.28pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:34.08pt;top:534pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:97.92pt;top:534pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:553.56pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:90.12pt;top:555.96pt;width:5.52pt;height:0.84pt"></div>
        <div class="r" style="left:90.12pt;top:558.12pt;width:5.52pt;height:0.84pt"></div>
        <div class="r" style="left:30.36pt;top:577.68pt;width:552.12pt;height:0.96pt"></div>
        <div class="r" style="left:321.24pt;top:608.88pt;width:5.52pt;height:0.84pt"></div>
        <div class="r" style="left:321.24pt;top:611.52pt;width:5.52pt;height:0.84pt"></div>
        <div class="r" style="left:95.28pt;top:647.28pt;width:370.92pt;height:0.96pt"></div>
        <div class="r" style="left:30.36pt;top:659.64pt;width:552.12pt;height:0.96pt"></div>
        <div class="t f100" style="left:111.48pt;top:179.72pt">1</div>
        <div class="t f100" style="left:148.56pt;top:179.72pt">2</div>
        <div class="t f100" style="left:185.64pt;top:179.72pt">3</div>
        <div class="t f100" style="left:222.72pt;top:179.72pt">4</div>
        <div class="t f100" style="left:259.8pt;top:179.72pt">5</div>
        <div class="t f100" style="left:296.88pt;top:179.72pt">6</div>
        <div class="t f100" style="left:333.96pt;top:179.72pt">7</div>
        <div class="t f100" style="left:371.04pt;top:179.72pt">8</div>
        <div class="t f100" style="left:408.12pt;top:179.72pt">9</div>
        <div class="t f100" style="left:442.44pt;top:179.72pt">10</div>
        <div class="t f100" style="left:34.56pt;top:197.48pt">A</div>
        <?php if(!empty($v['appraiserA'])): ?>
        <div class="t f66" style="left:42pt;top:199.48pt">(<?php echo e(\Illuminate\Support\Str::limit($v['appraiserA'], 12, '...')); ?>)</div>
        <?php endif; ?>
        <div class="t f100" style="left:57.72pt;top:197.48pt">1</div>
        <div class="t f100" style="left:60.24pt;top:214.88pt">2</div>
        <div class="t f100" style="left:60.24pt;top:232.28pt">3</div>
        <div class="t f100" style="left:56.4pt;top:250.64pt">Average</div>
        <div class="t f120" style="left:473.52pt;top:249.63pt">X</div>
        <div class="t f100" style="left:484.19pt;top:251.36pt">a</div>
        <div class="t f100" style="left:495.24pt;top:251.36pt">=</div>
        <div class="t f100" style="left:64.08pt;top:270.08pt">Range</div>
        <div class="t f120" style="left:474.94pt;top:269.07pt">R</div>
        <div class="t f100" style="left:486.36pt;top:270.8pt">a</div>
        <div class="t f100" style="left:497.4pt;top:270.8pt">=</div>
        <div class="t f100" style="left:34.57pt;top:288.56pt">B</div>
        <?php if(!empty($v['appraiserB'])): ?>
        <div class="t f66" style="left:42pt;top:290.56pt">(<?php echo e(\Illuminate\Support\Str::limit($v['appraiserB'], 12, '...')); ?>)</div>
        <?php endif; ?>
        <div class="t f100" style="left:57.72pt;top:288.56pt">1</div>
        <div class="t f100" style="left:60.24pt;top:305.96pt">2</div>
        <div class="t f100" style="left:60.24pt;top:323.36pt">3</div>
        <div class="t f100" style="left:56.4pt;top:341.72pt">Average</div>
        <div class="t f120" style="left:473.52pt;top:340.71pt">X</div>
        <div class="t f100" style="left:484.2pt;top:342.44pt">b</div>
        <div class="t f100" style="left:495.24pt;top:342.44pt">=</div>
        <div class="t f100" style="left:64.08pt;top:361.16pt">Range</div>
        <div class="t f120" style="left:474.94pt;top:360.15pt">R</div>
        <div class="t f100" style="left:486.36pt;top:361.88pt">b</div>
        <div class="t f100" style="left:497.4pt;top:361.88pt">=</div>
        <div class="t f100" style="left:34.57pt;top:379.64pt">C</div>
        <?php if(!empty($v['appraiserC'])): ?>
        <div class="t f66" style="left:42pt;top:381.64pt">(<?php echo e(\Illuminate\Support\Str::limit($v['appraiserC'], 12, '...')); ?>)</div>
        <?php endif; ?>
        <div class="t f100" style="left:58.32pt;top:379.64pt">1</div>
        <div class="t f100" style="left:60.24pt;top:397.04pt">2</div>
        <div class="t f100" style="left:60.24pt;top:414.44pt">3</div>
        <div class="t f100" style="left:56.4pt;top:432.8pt">Average</div>
        <div class="t f120" style="left:473.52pt;top:431.79pt">X</div>
        <div class="t f100" style="left:484.2pt;top:433.52pt">c</div>
        <div class="t f100" style="left:494.74pt;top:433.52pt">=</div>
        <div class="t f100" style="left:64.08pt;top:452.24pt">Range</div>
        <div class="t f120" style="left:474.94pt;top:451.23pt">R</div>
        <div class="t f100" style="left:486.36pt;top:452.96pt">c</div>
        <div class="t f100" style="left:496.91pt;top:452.96pt">=</div>
        <div class="t f120" style="left:473.52pt;top:470.67pt">X</div>
        <div class="t f100" style="left:495.24pt;top:472.4pt">=</div>
        <div class="t f120" style="left:474.94pt;top:490.11pt">R</div>
        <div class="t f100" style="left:486.36pt;top:491.84pt">p</div>
        <div class="t f100" style="left:497.4pt;top:491.84pt">=</div>
        <div class="t f120" style="left:473.52pt;top:511.95pt">R</div>
        <div class="t f100" style="left:495.96pt;top:513.68pt">=</div>
        <div class="t f120" style="left:474.94pt;top:535.71pt">X</div>
        <div class="t f66" style="left:485.64pt;top:542.21pt">DIFF</div>
        <div class="t f100" style="left:505.92pt;top:537.44pt">=</div>
        <div class="t f100" style="left:34.56pt;top:581pt">*</div>
        <div class="t f100" style="left:41.15pt;top:581pt">D</div>
        <div class="t f100" style="left:34.56pt;top:597.92pt">beyond</div>
        <div class="t f100" style="left:69.6pt;top:597.92pt">this</div>
        <div class="t f100" style="left:87.82pt;top:597.92pt">limit.</div>
        <div class="t f100" style="left:111.08pt;top:597.92pt">Identify</div>
        <div class="t f100" style="left:145.64pt;top:597.92pt">the</div>
        <div class="t f100" style="left:162.19pt;top:597.92pt">cause</div>
        <div class="t f100" style="left:191.56pt;top:597.92pt">and</div>
        <div class="t f100" style="left:210.88pt;top:597.92pt">correct.</div>
        <div class="t f100" style="left:246.97pt;top:597.92pt">Repeat</div>
        <div class="t f100" style="left:281.75pt;top:597.92pt">these</div>
        <div class="t f100" style="left:308.86pt;top:597.92pt">readings</div>
        <div class="t f100" style="left:349.76pt;top:597.92pt">using</div>
        <div class="t f100" style="left:376.26pt;top:597.92pt">the</div>
        <div class="t f100" style="left:392.82pt;top:597.92pt">same</div>
        <div class="t f100" style="left:420.15pt;top:597.92pt">appraiser</div>
        <div class="t f100" style="left:464.4pt;top:597.92pt">and</div>
        <div class="t f100" style="left:483.71pt;top:597.92pt">unit</div>
        <div class="t f100" style="left:502.42pt;top:597.92pt">as</div>
        <div class="t f100" style="left:34.56pt;top:614.36pt">originally</div>
        <div class="t f100" style="left:76.06pt;top:614.36pt">used</div>
        <div class="t f100" style="left:100.41pt;top:614.36pt">or</div>
        <div class="t f100" style="left:112.04pt;top:614.36pt">discard</div>
        <div class="t f100" style="left:146.93pt;top:614.36pt">values</div>
        <div class="t f100" style="left:178.35pt;top:614.36pt">and</div>
        <div class="t f100" style="left:197.67pt;top:614.36pt">re-average</div>
        <div class="t f100" style="left:248.52pt;top:614.36pt">and</div>
        <div class="t f100" style="left:267.83pt;top:614.36pt">recompute</div>
        <div class="t f100" style="left:317.85pt;top:614.36pt">R</div>
        <div class="t f100" style="left:327.8pt;top:614.36pt">and</div>
        <div class="t f100" style="left:347.11pt;top:614.36pt">the</div>
        <div class="t f100" style="left:363.67pt;top:614.36pt">limiting</div>
        <div class="t f100" style="left:397.36pt;top:614.36pt">value</div>
        <div class="t f100" style="left:423.76pt;top:614.36pt">from</div>
        <div class="t f100" style="left:446.77pt;top:614.36pt">the</div>
        <div class="t f100" style="left:463.32pt;top:614.36pt">remaining</div>
        <div class="t f100" style="left:509.86pt;top:614.36pt">observations.</div>
        <div class="t f100" style="left:40.08pt;top:632.72pt">Notes</div>
        <div class="t f100" style="left:68.85pt;top:632.72pt">:</div>
        <div class="t f60" style="left:48.36pt;top:584.36pt">4</div>
        <div class="t f100" style="left:53.4pt;top:581pt">=</div>
        <div class="t f100" style="left:61.92pt;top:581pt">3.27</div>
        <div class="t f100" style="left:84pt;top:581pt">for</div>
        <div class="t f100" style="left:98.49pt;top:581pt">2</div>
        <div class="t f100" style="left:106.77pt;top:581pt">trial</div>
        <div class="t f100" style="left:125.48pt;top:581pt">and</div>
        <div class="t f100" style="left:144.8pt;top:581pt">2.58</div>
        <div class="t f100" style="left:166.88pt;top:581pt">for</div>
        <div class="t f100" style="left:181.37pt;top:581pt">3</div>
        <div class="t f100" style="left:189.65pt;top:581pt">trial.</div>
        <div class="t f100" style="left:211.11pt;top:581pt">UCL</div>
        <div class="t f60" style="left:231.12pt;top:584.36pt">R</div>
        <div class="t f100" style="left:238.19pt;top:581pt">represents</div>
        <div class="t f100" style="left:288.06pt;top:581pt">the</div>
        <div class="t f100" style="left:304.62pt;top:581pt">limit</div>
        <div class="t f100" style="left:325.12pt;top:581pt">of</div>
        <div class="t f100" style="left:336.26pt;top:581pt">individual</div>
        <div class="t f100" style="left:380.18pt;top:581pt">R's.</div>
        <div class="t f100" style="left:399.83pt;top:581pt">Circle</div>
        <div class="t f100" style="left:427.98pt;top:581pt">those</div>
        <div class="t f100" style="left:455.08pt;top:581pt">that</div>
        <div class="t f100" style="left:474.4pt;top:581pt">are</div>
        <div class="t f120" style="left:34.56pt;top:511.47pt">(</div>
        <div class="t f100" style="left:41.27pt;top:513.2pt">[</div>
        <div class="t f100" style="left:46.79pt;top:513.2pt">R</div>
        <div class="t f100" style="left:56.74pt;top:513.2pt">a</div>
        <div class="t f100" style="left:65.02pt;top:513.2pt">=</div>
        <div class="t f100" style="left:90.09pt;top:513.2pt">]</div>
        <div class="t f100" style="left:95.61pt;top:513.2pt">+</div>
        <div class="t f100" style="left:104.12pt;top:513.2pt">[R</div>
        <div class="t f100" style="left:116.84pt;top:513.2pt">b</div>
        <div class="t f100" style="left:125.12pt;top:513.2pt">=</div>
        <div class="t f100" style="left:147.43pt;top:513.2pt">]</div>
        <div class="t f100" style="left:152.95pt;top:513.2pt">+</div>
        <div class="t f100" style="left:161.46pt;top:513.2pt">[</div>
        <div class="t f100" style="left:166.98pt;top:513.2pt">R</div>
        <div class="t f100" style="left:176.93pt;top:513.2pt">c</div>
        <div class="t f100" style="left:184.71pt;top:513.2pt">=</div>
        <div class="t f100" style="left:207.03pt;top:513.2pt">]</div>
        <div class="t f120" style="left:212.64pt;top:511.47pt">)</div>
        <div class="t f100" style="left:219.96pt;top:513.2pt">/</div>
        <div class="t f100" style="left:225.48pt;top:513.2pt">[</div>
        <div class="t f100" style="left:230.99pt;top:513.2pt">#</div>
        <div class="t f100" style="left:239.27pt;top:513.2pt">OF</div>
        <div class="t f100" style="left:255.94pt;top:513.2pt">APPRAISERS</div>
        <div class="t f100" style="left:322.03pt;top:513.2pt">=</div>
        <div class="t f100" style="left:349.87pt;top:513.2pt">]</div>
        <div class="t f100" style="left:355.39pt;top:513.2pt">=</div>
        <div class="t f100" style="left:34.56pt;top:536pt">X</div>
        <div class="t f66" style="left:44.04pt;top:540.77pt">DIFF</div>
        <div class="t f100" style="left:61.55pt;top:536pt">=</div>
        <div class="t f100" style="left:70.07pt;top:536pt">[Max</div>
        <div class="t f100" style="left:94.43pt;top:536pt">X</div>
        <div class="t f100" style="left:103.9pt;top:536pt">=</div>
        <div class="t f100" style="left:145.54pt;top:536pt">]</div>
        <div class="t f100" style="left:151.05pt;top:536pt">-</div>
        <div class="t f100" style="left:157.16pt;top:536pt">[</div>
        <div class="t f100" style="left:162.67pt;top:536pt">Min</div>
        <div class="t f100" style="left:181.39pt;top:536pt">X</div>
        <div class="t f100" style="left:190.87pt;top:536pt">=</div>
        <div class="t f100" style="left:221.46pt;top:536pt">]</div>
        <div class="t f100" style="left:226.97pt;top:536pt">=</div>
        <div class="t f100" style="left:34.56pt;top:560.12pt">*</div>
        <div class="t f100" style="left:41.15pt;top:560.12pt">UCL</div>
        <div class="t f66" style="left:63.84pt;top:564.89pt">R</div>
        <div class="t f100" style="left:71.39pt;top:560.12pt">=</div>
        <div class="t f100" style="left:79.92pt;top:560.12pt">[</div>
        <div class="t f100" style="left:85.44pt;top:560.12pt">R</div>
        <div class="t f100" style="left:95.39pt;top:560.12pt">=</div>
        <div class="t f100" style="left:134.26pt;top:560.12pt">]</div>
        <div class="t f100" style="left:139.79pt;top:560.12pt">x</div>
        <div class="t f100" style="left:147.57pt;top:560.12pt">[</div>
        <div class="t f100" style="left:153.1pt;top:560.12pt">D</div>
        <div class="t f66" style="left:162.12pt;top:564.89pt">4</div>
        <div class="t f100" style="left:168.6pt;top:560.12pt">=</div>
        <div class="t f100" style="left:213pt;top:560.12pt">]</div>
        <div class="t f100" style="left:218.52pt;top:560.12pt">=</div>
        <div class="t f110 b" style="left:37.08pt;top:162.73pt">Appraiser</div>
        <div class="t f110 b" style="left:266.4pt;top:160.69pt">PART</div>
        <div class="t f110 b" style="left:497.64pt;top:169.81pt">AVERAGE</div>
        <div class="t f110 b" style="left:44.88pt;top:176.89pt">/Trial</div>
        <div class="t f110 b" style="left:74.33pt;top:176.89pt">#</div>
        <div class="t f120 b" style="left:131.28pt;top:138.03pt">Gage</div>
        <div class="t f120 b" style="left:164.7pt;top:138.03pt">Repeatability</div>
        <div class="t f120 b" style="left:242.49pt;top:138.03pt">and</div>
        <div class="t f120 b" style="left:267.19pt;top:138.03pt">Reproducibility</div>
        <div class="t f120 b" style="left:357.58pt;top:138.03pt">Data</div>
        <div class="t f120 b" style="left:386.95pt;top:138.03pt">Collection</div>
        <div class="t f120 b" style="left:448.32pt;top:138.03pt">Sheet</div>
        <div class="t f100" style="left:34.08pt;top:480.92pt">Part</div>
        <div class="t f100" style="left:55.07pt;top:480.92pt">Average</div>

        
        <?php
            // x (pt) garis vertikal kolom PART -> tepi kanan teks = garis - 1.5pt
            $pl   = [94.8, 131.88, 168.96, 206.04, 243.12, 280.2, 317.28, 354.36, 391.44, 428.52, 465.6];
            $tTop = ['A' => [197.5, 214.9, 232.3], 'B' => [288.5, 305.9, 323.3], 'C' => [379.6, 397.0, 414.4]];
            $aTop = ['A' => 250.6, 'B' => 341.7, 'C' => 432.8];   // baris Average
            $rTop = ['A' => 270.1, 'B' => 361.1, 'C' => 452.2];   // baris Range
            $pTop = 480.9;                                         // baris Part Average
        ?>
        <?php $__currentLoopData = ['A', 'B', 'C']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php for($p = 1; $p <= 10; $p++): ?>
                <?php $left = $pl[$p] - 1.5 - 60; ?>
                <?php $__currentLoopData = [1, 2, 3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="v" style="left:<?php echo e($left); ?>pt;top:<?php echo e($tTop[$a][$t - 1]); ?>pt;width:60pt;text-align:right"><?php echo e($v["t_{$a}_{$t}_{$p}"] ?? ''); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="v" style="left:<?php echo e($left); ?>pt;top:<?php echo e($aTop[$a]); ?>pt;width:60pt;text-align:right"><?php echo e($o["avg_{$a}_{$p}"] ?? ''); ?></div>
                <div class="v" style="left:<?php echo e($left); ?>pt;top:<?php echo e($rTop[$a]); ?>pt;width:60pt;text-align:right"><?php echo e($o["rng_{$a}_{$p}"] ?? ''); ?></div>
            <?php endfor; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php for($p = 1; $p <= 10; $p++): ?>
            <div class="v" style="left:<?php echo e($pl[$p] - 1.5 - 60); ?>pt;top:<?php echo e($pTop); ?>pt;width:60pt;text-align:right"><?php echo e($o["pavg_{$p}"] ?? ''); ?></div>
        <?php endfor; ?>

        
        <div class="v" style="left:503.7pt;top:250.61pt;width:60pt;text-align:right"><?php echo e($o['xa'] ?? ''); ?></div>
        <div class="v" style="left:520.1pt;top:273.42pt;width:60pt;text-align:right"><?php echo e($o['ra'] ?? ''); ?></div>
        <div class="v" style="left:503.7pt;top:345.12pt;width:60pt;text-align:right"><?php echo e($o['xb'] ?? ''); ?></div>
        <div class="v" style="left:520.1pt;top:364.52pt;width:60pt;text-align:right"><?php echo e($o['rb'] ?? ''); ?></div>
        <div class="v" style="left:503.7pt;top:436.12pt;width:60pt;text-align:right"><?php echo e($o['xc'] ?? ''); ?></div>
        <div class="v" style="left:520.1pt;top:455.62pt;width:60pt;text-align:right"><?php echo e($o['rc'] ?? ''); ?></div>
        <div class="v" style="left:503.7pt;top:475.02pt;width:60pt;text-align:right"><?php echo e($o['p1_xbar'] ?? ''); ?></div>
        <div class="v" style="left:503.7pt;top:494.52pt;width:60pt;text-align:right"><?php echo e($o['p1_rp'] ?? ''); ?></div>
        <div class="v" style="left:520.1pt;top:518.62pt;width:60pt;text-align:right"><?php echo e($o['p1_rbb'] ?? ''); ?></div>
        <div class="v" style="left:503.7pt;top:542.71pt;width:60pt;text-align:right"><?php echo e($o['p1_xdiff'] ?? ''); ?></div>
        <div class="v" style="left:72.3pt;top:513.21pt;width:16.3pt;text-align:center"><?php echo e($o['f_ra'] ?? ''); ?></div>
        <div class="v" style="left:132.4pt;top:513.21pt;width:13.5pt;text-align:center"><?php echo e($o['f_rb'] ?? ''); ?></div>
        <div class="v" style="left:192pt;top:513.21pt;width:13.5pt;text-align:center"><?php echo e($o['f_rc'] ?? ''); ?></div>
        <div class="v" style="left:329.3pt;top:513.21pt;width:19.1pt;text-align:center"><?php echo e($o['f_nap'] ?? ''); ?></div>
        <div class="v" style="left:364.5pt;top:513.21pt;width:96pt;text-align:center"><?php echo e($o['f_rbar_sum'] ?? ''); ?></div>
        <div class="v" style="left:111.2pt;top:536.01pt;width:32.8pt;text-align:center"><?php echo e($o['f_max'] ?? ''); ?></div>
        <div class="v" style="left:198.2pt;top:536.01pt;width:21.8pt;text-align:center"><?php echo e($o['f_min'] ?? ''); ?></div>
        <div class="v" style="left:235.5pt;top:536.01pt;width:225pt;text-align:center"><?php echo e($o['f_xdiff_res'] ?? ''); ?></div>
        <div class="v" style="left:102.7pt;top:560.12pt;width:30.1pt;text-align:center"><?php echo e($o['f_rbar'] ?? ''); ?></div>
        <div class="v" style="left:175.9pt;top:560.12pt;width:35.6pt;text-align:center"><?php echo e($o['f_d4'] ?? ''); ?></div>
        <div class="v" style="left:227.5pt;top:560.12pt;width:233pt;text-align:center"><?php echo e($o['f_ucl'] ?? ''); ?></div>
    </div>

    
    <div class="page">
        <div class="r" style="left:35.28pt;top:110.16pt;width:0.96pt;height:570.84pt"></div>
        <div class="r" style="left:36.24pt;top:110.16pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:575.64pt;top:111.12pt;width:0.96pt;height:569.88pt"></div>
        <div class="r" style="left:36.24pt;top:130.44pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:121.2pt;top:208.44pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:121.2pt;top:211.32pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:246.6pt;top:211.32pt;width:7.2pt;height:0.84pt"></div>
        <div class="r" style="left:36.24pt;top:227.64pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:403.68pt;top:228.6pt;width:0.96pt;height:427.68pt"></div>
        <div class="r" style="left:36.24pt;top:247.08pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:303.48pt;top:266.52pt;width:0.96pt;height:59.28pt"></div>
        <div class="r" style="left:304.44pt;top:266.52pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:353.16pt;top:267.48pt;width:0.96pt;height:58.32pt"></div>
        <div class="r" style="left:144.12pt;top:268.92pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:144.12pt;top:271.8pt;width:7.92pt;height:0.84pt"></div>
        <div class="r" style="left:304.44pt;top:285.96pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:304.44pt;top:305.4pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:36.24pt;top:324.84pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:159.12pt;top:344.52pt;width:142.68pt;height:0.84pt"></div>
        <div class="r" style="left:230.88pt;top:383.16pt;width:0.96pt;height:39.84pt"></div>
        <div class="r" style="left:231.84pt;top:383.16pt;width:172.8pt;height:0.96pt"></div>
        <div class="r" style="left:303.48pt;top:384.12pt;width:0.96pt;height:38.88pt"></div>
        <div class="r" style="left:353.16pt;top:384.12pt;width:0.96pt;height:38.88pt"></div>
        <div class="r" style="left:231.84pt;top:402.6pt;width:172.8pt;height:0.96pt"></div>
        <div class="r" style="left:36.24pt;top:422.04pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:149.76pt;top:444.6pt;width:76.44pt;height:0.84pt"></div>
        <div class="r" style="left:303.48pt;top:460.92pt;width:0.96pt;height:195.36pt"></div>
        <div class="r" style="left:304.44pt;top:460.92pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:353.16pt;top:461.88pt;width:0.96pt;height:194.4pt"></div>
        <div class="r" style="left:304.44pt;top:480.36pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:36.24pt;top:499.8pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:304.44pt;top:519.24pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:304.44pt;top:538.68pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:304.44pt;top:558.12pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:36.24pt;top:577.56pt;width:540.36pt;height:0.96pt"></div>
        <div class="r" style="left:304.44pt;top:597pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:148.92pt;top:599.4pt;width:76.44pt;height:0.84pt"></div>
        <div class="r" style="left:304.44pt;top:616.44pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:304.44pt;top:635.88pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:304.44pt;top:655.32pt;width:100.2pt;height:0.96pt"></div>
        <div class="r" style="left:36.24pt;top:680.04pt;width:540.36pt;height:0.96pt"></div>
        <img class="rad" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MTJwdCIgaGVpZ2h0PSI3OTJwdCIgdmlld0JveD0iMCAwIDYxMiA3OTIiPjxwb2x5Z29uIHBvaW50cz0iMTU4LjA0LDM0NC43NiAxNTAuOTYsMzYyLjg4IDE1MS41NiwzNjMuMTIgMTU4Ljc2LDM0NS4xMiIgZmlsbD0iIzAwMCIvPjxwb2x5Z29uIHBvaW50cz0iMTQ2LjA0LDM0OS44MCAxNTIuNDAsMzYzLjQ4IDE1MS42OCwzNjMuODQgMTQ1LjQ0LDM1MC4wNCIgZmlsbD0iIzAwMCIvPjxwb2x5Z29uIHBvaW50cz0iMTQ5LjA0LDQ0NC45NiAxNDUuMjAsNDYwLjkyIDE0NS45Miw0NjEuMTYgMTQ5Ljc2LDQ0NS4wOCIgZmlsbD0iIzAwMCIvPjxwb2x5Z29uIHBvaW50cz0iMTQyLjkyLDQ0OS4yOCAxNDYuNDAsNDYxLjUyIDE0NS42OCw0NjEuNjQgMTQyLjIwLDQ0OS41MiIgZmlsbD0iIzAwMCIvPjxwb2x5Z29uIHBvaW50cz0iMTQ4LjIwLDU5OS43NiAxNDQuMzYsNjEzLjgwIDE0NS4wOCw2MTMuOTIgMTQ4LjkyLDU5OS44OCIgZmlsbD0iIzAwMCIvPjxwb2x5Z29uIHBvaW50cz0iMTQyLjA4LDYwMy42MCAxNDUuNTYsNjE0LjI4IDE0NC44NCw2MTQuNDAgMTQxLjM2LDYwMy44NCIgZmlsbD0iIzAwMCIvPjwvc3ZnPg==">
        <div class="t f100" style="left:37.68pt;top:139.04pt">Part</div>
        <div class="t f100" style="left:58.67pt;top:139.04pt">No.</div>
        <div class="t f100" style="left:76.89pt;top:139.04pt">&amp;</div>
        <div class="t f100" style="left:86.25pt;top:139.04pt">Name</div>
        <div class="t f100" style="left:115.75pt;top:139.04pt">:</div>
        <div class="t f100" style="left:233.28pt;top:139.04pt">Gage</div>
        <div class="t f100" style="left:260.39pt;top:139.04pt">Name</div>
        <div class="t f100" style="left:292.65pt;top:139.04pt">:</div>
        <div class="t f100" style="left:406.08pt;top:139.04pt">Date</div>
        <div class="t f100" style="left:462.94pt;top:139.04pt">:</div>
        <div class="t f100" style="left:37.68pt;top:158.48pt">Characteristics</div>
        <div class="t f100" style="left:114.65pt;top:158.48pt">:</div>
        <div class="t f100" style="left:406.08pt;top:158.48pt">Performed</div>
        <div class="t f100" style="left:455.61pt;top:158.48pt">by</div>
        <div class="t f100" style="left:468.57pt;top:158.48pt">:</div>
        <div class="t f100" style="left:37.68pt;top:177.92pt">Specifications</div>
        <div class="t f100" style="left:112.87pt;top:177.92pt">:</div>
        <div class="t f100" style="left:37.68pt;top:213.44pt">From</div>
        <div class="t f100" style="left:63.92pt;top:213.44pt">data</div>
        <div class="t f100" style="left:85.99pt;top:213.44pt">sheet</div>
        <div class="t f100" style="left:113.09pt;top:213.44pt">:</div>
        <div class="t f100" style="left:118.62pt;top:213.44pt">R</div>
        <div class="t f100" style="left:128.56pt;top:213.44pt">=</div>
        <div class="t f100" style="left:246.71pt;top:213.44pt">X</div>
        <div class="t f60" style="left:256.2pt;top:216.8pt">DIFF</div>
        <div class="t f100" style="left:286.44pt;top:213.44pt">=</div>
        <div class="t f100" style="left:432.48pt;top:213.44pt">R</div>
        <div class="t f80" style="left:442.44pt;top:215.07pt">p</div>
        <div class="t f100" style="left:467.4pt;top:213.44pt">=</div>
        <div class="t f100" style="left:87.84pt;top:275.12pt">EV</div>
        <div class="t f100" style="left:131.39pt;top:275.12pt">=</div>
        <div class="t f100" style="left:143.75pt;top:274.88pt">R</div>
        <div class="t f100" style="left:159.22pt;top:274.88pt">X</div>
        <div class="t f100" style="left:171.45pt;top:274.88pt">K</div>
        <div class="t f70" style="left:178.08pt;top:277.42pt">1</div>
        <div class="t f100" style="left:317.04pt;top:275.12pt">Trials</div>
        <div class="t f100" style="left:372.48pt;top:274.88pt">K</div>
        <div class="t f80" style="left:381.84pt;top:276.51pt">1</div>
        <div class="t f100" style="left:426pt;top:275.12pt">%</div>
        <div class="t f100" style="left:437.63pt;top:275.12pt">EV</div>
        <div class="t f100" style="left:467.39pt;top:275.12pt">=</div>
        <div class="t f100" style="left:477pt;top:275.12pt">100</div>
        <div class="t f100" style="left:496.32pt;top:275.12pt">[</div>
        <div class="t f100" style="left:501.83pt;top:275.12pt">EV</div>
        <div class="t f100" style="left:517.79pt;top:275.12pt">/</div>
        <div class="t f100" style="left:523.32pt;top:275.12pt">TV</div>
        <div class="t f100" style="left:538.89pt;top:275.12pt">]</div>
        <div class="t f100" style="left:131.4pt;top:294.56pt">=</div>
        <div class="t f100" style="left:326.52pt;top:294.56pt">2</div>
        <div class="t f100" style="left:371.88pt;top:294.56pt">0.8862</div>
        <div class="t f100" style="left:467.4pt;top:294.56pt">=</div>
        <div class="t f100" style="left:326.52pt;top:314pt">3</div>
        <div class="t f100" style="left:371.88pt;top:314pt">0.5908</div>
        <div class="t f100" style="left:87.84pt;top:351.32pt">AV</div>
        <div class="t f100" style="left:131.39pt;top:351.32pt">=</div>
        <div class="t f100" style="left:428.76pt;top:351.32pt">%</div>
        <div class="t f100" style="left:440.39pt;top:351.32pt">AV</div>
        <div class="t f100" style="left:467.39pt;top:351.32pt">=</div>
        <div class="t f100" style="left:477pt;top:351.32pt">100</div>
        <div class="t f100" style="left:496.32pt;top:351.32pt">[</div>
        <div class="t f100" style="left:501.83pt;top:351.32pt">AV</div>
        <div class="t f100" style="left:517.79pt;top:351.32pt">/</div>
        <div class="t f100" style="left:523.32pt;top:351.32pt">TV</div>
        <div class="t f100" style="left:538.89pt;top:351.32pt">]</div>
        <div class="t f100" style="left:131.4pt;top:372.32pt">=</div>
        <div class="t f100" style="left:467.4pt;top:372.32pt">=</div>
        <div class="t f100" style="left:244.32pt;top:391.76pt">Appraisers</div>
        <div class="t f100" style="left:326.52pt;top:391.76pt">2</div>
        <div class="t f100" style="left:376.56pt;top:391.76pt">3</div>
        <div class="t f100" style="left:43.2pt;top:409.64pt">n</div>
        <div class="t f100" style="left:54.23pt;top:409.64pt">=</div>
        <div class="t f100" style="left:65.52pt;top:409.64pt">part</div>
        <div class="t f100" style="left:96.45pt;top:409.64pt">r</div>
        <div class="t f100" style="left:102.57pt;top:409.64pt">=</div>
        <div class="t f100" style="left:111.08pt;top:409.64pt">trials</div>
        <div class="t f100" style="left:261.6pt;top:409.04pt">K</div>
        <div class="t f66" style="left:270.96pt;top:413.81pt">2</div>
        <div class="t f100" style="left:321.36pt;top:409.64pt">0.7071</div>
        <div class="t f100" style="left:371.88pt;top:409.64pt">0.5231</div>
        <div class="t f100" style="left:84.36pt;top:450.08pt">GRR</div>
        <div class="t f100" style="left:131.37pt;top:450.08pt">=</div>
        <div class="t f100" style="left:152.03pt;top:449.84pt">EV</div>
        <div class="t f66" style="left:165.24pt;top:447.77pt">2</div>
        <div class="t f100" style="left:174.48pt;top:449.84pt">+</div>
        <div class="t f100" style="left:182.99pt;top:449.84pt">AV</div>
        <div class="t f66" style="left:196.2pt;top:447.77pt">2</div>
        <div class="t f100" style="left:419.76pt;top:450.08pt">%</div>
        <div class="t f100" style="left:431.39pt;top:450.08pt">GRR</div>
        <div class="t f100" style="left:467.36pt;top:450.08pt">=</div>
        <div class="t f100" style="left:477pt;top:450.08pt">100</div>
        <div class="t f100" style="left:496.32pt;top:450.08pt">[</div>
        <div class="t f100" style="left:501.83pt;top:450.08pt">GRR</div>
        <div class="t f100" style="left:526.77pt;top:450.08pt">/</div>
        <div class="t f100" style="left:532.29pt;top:450.08pt">TV</div>
        <div class="t f100" style="left:547.87pt;top:450.08pt">]</div>
        <div class="t f100" style="left:131.4pt;top:467.96pt">=</div>
        <div class="t f100" style="left:317.64pt;top:467.96pt">Parts</div>
        <div class="t f100" style="left:372.84pt;top:467.36pt">K</div>
        <div class="t f66" style="left:382.2pt;top:472.13pt">3</div>
        <div class="t f100" style="left:467.4pt;top:467.96pt">=</div>
        <div class="t f100" style="left:326.52pt;top:488.96pt">2</div>
        <div class="t f100" style="left:371.88pt;top:488.96pt">0.7071</div>
        <div class="t f100" style="left:37.68pt;top:508.4pt">Part</div>
        <div class="t f100" style="left:58.67pt;top:508.4pt">Variation</div>
        <div class="t f100" style="left:100.53pt;top:508.4pt">(</div>
        <div class="t f100" style="left:106.63pt;top:508.4pt">PV</div>
        <div class="t f100" style="left:122.59pt;top:508.4pt">)</div>
        <div class="t f100" style="left:326.52pt;top:508.4pt">3</div>
        <div class="t f100" style="left:371.88pt;top:508.4pt">0.5231</div>
        <div class="t f100" style="left:87.84pt;top:526.28pt">PV</div>
        <div class="t f100" style="left:131.39pt;top:526.28pt">=</div>
        <div class="t f100" style="left:143.75pt;top:525.68pt">R</div>
        <div class="t f66" style="left:153.72pt;top:530.45pt">p</div>
        <div class="t f100" style="left:162.96pt;top:525.68pt">X</div>
        <div class="t f100" style="left:175.18pt;top:525.68pt">K</div>
        <div class="t f66" style="left:184.56pt;top:530.45pt">3</div>
        <div class="t f100" style="left:326.52pt;top:526.28pt">4</div>
        <div class="t f100" style="left:371.88pt;top:526.28pt">0.4467</div>
        <div class="t f100" style="left:426pt;top:526.28pt">%</div>
        <div class="t f100" style="left:437.63pt;top:526.28pt">PV</div>
        <div class="t f100" style="left:467.38pt;top:526.28pt">=</div>
        <div class="t f100" style="left:477pt;top:526.28pt">100</div>
        <div class="t f100" style="left:496.31pt;top:526.28pt">[</div>
        <div class="t f100" style="left:501.83pt;top:526.28pt">PV</div>
        <div class="t f100" style="left:517.79pt;top:526.28pt">/</div>
        <div class="t f100" style="left:523.31pt;top:526.28pt">TV</div>
        <div class="t f100" style="left:538.89pt;top:526.28pt">]</div>
        <div class="t f100" style="left:131.4pt;top:547.28pt">=</div>
        <div class="t f100" style="left:326.52pt;top:547.28pt">5</div>
        <div class="t f100" style="left:371.88pt;top:547.28pt">0.4030</div>
        <div class="t f100" style="left:467.4pt;top:547.28pt">=</div>
        <div class="t f100" style="left:326.52pt;top:566.72pt">6</div>
        <div class="t f100" style="left:371.88pt;top:566.72pt">0.3742</div>
        <div class="t f100" style="left:37.68pt;top:586.16pt">Total</div>
        <div class="t f100" style="left:62.62pt;top:586.16pt">Variation</div>
        <div class="t f100" style="left:104.47pt;top:586.16pt">(</div>
        <div class="t f100" style="left:110.58pt;top:586.16pt">TV</div>
        <div class="t f100" style="left:126.17pt;top:586.16pt">)</div>
        <div class="t f100" style="left:326.52pt;top:586.16pt">7</div>
        <div class="t f100" style="left:371.88pt;top:586.16pt">0.3534</div>
        <div class="t f100" style="left:85.44pt;top:605.6pt">TV</div>
        <div class="t f100" style="left:131.38pt;top:605.6pt">=</div>
        <div class="t f100" style="left:149.28pt;top:605.36pt">GRR</div>
        <div class="t f66" style="left:171.48pt;top:603.29pt">2</div>
        <div class="t f100" style="left:179.76pt;top:605.36pt">+</div>
        <div class="t f100" style="left:191.05pt;top:605.36pt">PV</div>
        <div class="t f66" style="left:204.24pt;top:603.29pt">2</div>
        <div class="t f100" style="left:326.52pt;top:605.6pt">8</div>
        <div class="t f100" style="left:371.88pt;top:605.6pt">0.3375</div>
        <div class="t f100" style="left:432pt;top:605.6pt">ndc</div>
        <div class="t f100" style="left:467.39pt;top:605.6pt">=</div>
        <div class="t f100" style="left:477pt;top:605.36pt">1.41</div>
        <div class="t f100" style="left:499.08pt;top:605.36pt">[</div>
        <div class="t f80" style="left:504.6pt;top:606.99pt">PV</div>
        <div class="t f80" style="left:517.65pt;top:606.99pt">/</div>
        <div class="t f80" style="left:522.2pt;top:606.99pt">GRR</div>
        <div class="t f100" style="left:542.75pt;top:605.36pt">]</div>
        <div class="t f100" style="left:131.4pt;top:625.04pt">=</div>
        <div class="t f100" style="left:326.52pt;top:625.04pt">9</div>
        <div class="t f100" style="left:371.88pt;top:625.04pt">0.3249</div>
        <div class="t f100" style="left:467.4pt;top:625.04pt">=</div>
        <div class="t f100" style="left:323.76pt;top:644.48pt">10</div>
        <div class="t f100" style="left:371.88pt;top:644.48pt">0.3146</div>
        <div class="t f100" style="left:154.8pt;top:351.32pt">(</div>
        <div class="t f100" style="left:160.91pt;top:351.32pt">X</div>
        <div class="t f100" style="left:37.68pt;top:430.64pt">Repeatibility</div>
        <div class="t f100" style="left:94.06pt;top:430.64pt">&amp;</div>
        <div class="t f100" style="left:103.41pt;top:430.64pt">Reproducibility</div>
        <div class="t f100" style="left:170.91pt;top:430.64pt">(</div>
        <div class="t f100" style="left:177.02pt;top:430.64pt">GRR</div>
        <div class="t f100" style="left:201.96pt;top:430.64pt">)</div>
        <div class="t f66" style="left:170.4pt;top:356.09pt">DIFF</div>
        <div class="t f100" style="left:187.92pt;top:351.32pt">X</div>
        <div class="t f100" style="left:197.4pt;top:351.32pt">K</div>
        <div class="t f66" style="left:206.76pt;top:356.09pt">2</div>
        <div class="t f100" style="left:210.48pt;top:351.32pt">)</div>
        <div class="t f66" style="left:213.84pt;top:349.25pt">2</div>
        <div class="t f100" style="left:220.32pt;top:351.32pt">-</div>
        <div class="t f100" style="left:226.44pt;top:351.32pt">(</div>
        <div class="t f100" style="left:232.54pt;top:351.32pt">EV</div>
        <div class="t f66" style="left:245.76pt;top:349.25pt">2</div>
        <div class="t f100" style="left:252.24pt;top:351.32pt">/</div>
        <div class="t f100" style="left:257.76pt;top:351.32pt">(nr)</div>
        <div class="t f100" style="left:276.08pt;top:351.32pt">)</div>
        <div class="t f100 b" style="left:154.08pt;top:232.52pt">Measurement</div>
        <div class="t f100 b" style="left:221.22pt;top:232.52pt">Unit</div>
        <div class="t f100 b" style="left:243.4pt;top:232.52pt">Analisys</div>
        <div class="t f100 b" style="left:436.2pt;top:232.52pt">%</div>
        <div class="t f100 b" style="left:447.72pt;top:232.52pt">Total</div>
        <div class="t f100 b" style="left:474.44pt;top:232.52pt">Variation</div>
        <div class="t f100 b" style="left:519.77pt;top:232.52pt">(</div>
        <div class="t f100 b" style="left:525.88pt;top:232.52pt">TV</div>
        <div class="t f100 b" style="left:541.47pt;top:232.52pt">)</div>
        <div class="t f100" style="left:233.28pt;top:158.48pt">Gage</div>
        <div class="t f100" style="left:260.39pt;top:158.48pt">No</div>
        <div class="t f100" style="left:289.65pt;top:158.48pt">:</div>
        <div class="t f100" style="left:233.28pt;top:177.92pt">Gage</div>
        <div class="t f100" style="left:260.39pt;top:177.92pt">Type</div>
        <div class="t f100" style="left:290.62pt;top:177.92pt">:</div>
        <div class="t f100" style="left:37.68pt;top:255.68pt">Repeatibility</div>
        <div class="t f100" style="left:94.05pt;top:255.68pt">-</div>
        <div class="t f100" style="left:100.16pt;top:255.68pt">Equipment</div>
        <div class="t f100" style="left:150.55pt;top:255.68pt">Variation</div>
        <div class="t f100" style="left:192.39pt;top:255.68pt">(</div>
        <div class="t f100" style="left:198.5pt;top:255.68pt">EV</div>
        <div class="t f100" style="left:214.46pt;top:255.68pt">)</div>
        <div class="t f100" style="left:37.68pt;top:333.44pt">Reproducibility</div>
        <div class="t f100" style="left:105.2pt;top:333.44pt">-</div>
        <div class="t f100" style="left:111.31pt;top:333.44pt">Appraiser</div>
        <div class="t f100" style="left:156.63pt;top:333.44pt">Variation</div>
        <div class="t f100" style="left:198.5pt;top:333.44pt">(</div>
        <div class="t f100" style="left:204.61pt;top:333.44pt">AV</div>
        <div class="t f100" style="left:220.58pt;top:333.44pt">)</div>
        <div class="t f120 b" style="left:173.76pt;top:114.51pt">Gage</div>
        <div class="t f120 b" style="left:207.19pt;top:114.51pt">Repeatability</div>
        <div class="t f120 b" style="left:284.98pt;top:114.51pt">and</div>
        <div class="t f120 b" style="left:309.68pt;top:114.51pt">Reproducibility</div>
        <div class="t f120 b" style="left:400.08pt;top:114.51pt">Report</div>

        
        <div class="v" style="left:122pt;top:139.01pt;width:108pt;text-align:left;font-size:<?php echo e(min($v['h_part_fs'] ?? 10, $fit($v['h_part'] ?? '', 108))); ?>pt"><?php echo e($v['h_part'] ?? ''); ?></div>
        <div class="v" style="left:298pt;top:139.01pt;width:106pt;text-align:left;font-size:<?php echo e(min($v['h_gname_fs'] ?? 10, $fit($v['h_gname'] ?? '', 106))); ?>pt"><?php echo e($v['h_gname'] ?? ''); ?></div>
        <div class="v" style="left:468.5pt;top:139.01pt;width:106pt;text-align:left;font-size:<?php echo e(min($v['h_date_fs'] ?? 10, $fit($v['h_date'] ?? '', 106))); ?>pt"><?php echo e($v['h_date'] ?? ''); ?></div>
        <div class="v" style="left:120pt;top:158.51pt;width:111pt;text-align:left;font-size:<?php echo e(min($v['h_char_fs'] ?? 10, $fit($v['h_char'] ?? '', 111))); ?>pt"><?php echo e($v['h_char'] ?? ''); ?></div>
        <div class="v" style="left:295pt;top:158.51pt;width:108pt;text-align:left;font-size:<?php echo e(min($v['h_gno_fs'] ?? 10, $fit($v['h_gno'] ?? '', 108))); ?>pt"><?php echo e($v['h_gno'] ?? ''); ?></div>
        <div class="v" style="left:474.5pt;top:158.51pt;width:100pt;text-align:left;font-size:<?php echo e(min($v['h_by_fs'] ?? 10, $fit($v['h_by'] ?? '', 100))); ?>pt"><?php echo e($v['h_by'] ?? ''); ?></div>
        <div class="v" style="left:118pt;top:177.91pt;width:113pt;text-align:left;font-size:<?php echo e(min($v['h_spec_fs'] ?? 10, $fit($v['h_spec'] ?? '', 113))); ?>pt"><?php echo e($v['h_spec'] ?? ''); ?></div>
        <div class="v" style="left:296pt;top:177.91pt;width:278pt;text-align:left;font-size:<?php echo e(min($v['h_gtype_fs'] ?? 10, $fit($v['h_gtype'] ?? '', 278))); ?>pt"><?php echo e($v['h_gtype'] ?? ''); ?></div>
        <div class="v" style="left:169.4pt;top:212.91pt;width:60pt;text-align:right"><?php echo e($o['p2_rbb'] ?? ''); ?></div>
        <div class="v" style="left:310.9pt;top:212.91pt;width:60pt;text-align:right"><?php echo e($o['p2_xdiff'] ?? ''); ?></div>
        <div class="v" style="left:482.5pt;top:212.91pt;width:60pt;text-align:right"><?php echo e($o['p2_rp'] ?? ''); ?></div>
        <div class="v" style="left:123.7pt;top:294.52pt;width:60pt;text-align:right"><?php echo e($o['ev'] ?? ''); ?></div>
        <div class="v" style="left:123.7pt;top:372.31pt;width:60pt;text-align:right"><?php echo e($o['av'] ?? ''); ?></div>
        <div class="v" style="left:123.7pt;top:467.92pt;width:60pt;text-align:right"><?php echo e($o['grr'] ?? ''); ?></div>
        <div class="v" style="left:123.7pt;top:547.31pt;width:60pt;text-align:right"><?php echo e($o['pv'] ?? ''); ?></div>
        <div class="v" style="left:123.7pt;top:625.01pt;width:60pt;text-align:right"><?php echo e($o['tv'] ?? ''); ?></div>
        <div class="v" style="left:461.6pt;top:294.52pt;width:60pt;text-align:right"><?php echo e($o['pev'] ?? ''); ?></div>
        <div class="v" style="left:461.6pt;top:372.31pt;width:60pt;text-align:right"><?php echo e($o['pav'] ?? ''); ?></div>
        <div class="v" style="left:461.6pt;top:467.92pt;width:60pt;text-align:right"><?php echo e($o['pgrr'] ?? ''); ?></div>
        <div class="v" style="left:461.6pt;top:547.31pt;width:60pt;text-align:right"><?php echo e($o['ppv'] ?? ''); ?></div>
        <div class="v" style="left:461.6pt;top:625.01pt;width:60pt;text-align:right"><?php echo e($o['ndc'] ?? ''); ?></div>
    </div>
</body>
</html><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/pdf/msa-gage-rr.blade.php ENDPATH**/ ?>