{{-- MSA Gage R&R Preview Embed for Review Page --}}
@php
    use App\Helpers\SpecificationHelper;

    // Helper functions
    $num = function ($x) {
        if (is_string($x)) { $x = trim(str_replace(',', '.', $x)); }
        return ($x !== null && $x !== '' && is_numeric($x)) ? (float) $x : null;
    };
    $fmt = fn($x, $d) => ($x === null || is_nan($x) || is_infinite($x)) ? '#DIV/0!' : number_format($x, $d, '.', '');
    $mean = fn(array $a) => count($a) ? array_sum($a) / count($a) : null;

    // Get form data
    $v = $msaGageRr->form_data_json ?? [];
    $h = $msaGageRr->header_data ?? [];
    $general = $msaGageRr->general ?? [];
    
    // Parse specifications
    $specs = $general['specifications'] ?? null;
    $specsFormatted = SpecificationHelper::format($specs, 2);
    $specsWithRange = SpecificationHelper::formatWithRange($specs, 2);
    
    // Calculations
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
                $m = array_sum($vals) / count($vals);
                $rg = max($vals) - min($vals);
                $xs[] = $m; $rs[] = $rg;
                $partAvg[$p][] = $m;
                $r = max($r, count($vals));
                $o["avg_{$a}_{$p}"] = $fmt($m, 2);
                $o["rng_{$a}_{$p}"] = $fmt($rg, 2);
            }
        }
        if (count($xs)) { $X[$a] = $mean($xs); $R[$a] = $mean($rs); }
    }
    
    $pAvg = [];
    for ($p = 1; $p <= 10; $p++) {
        if (isset($partAvg[$p])) { $pAvg[$p] = $mean($partAvg[$p]); }
    }

    $n = count($pAvg);
    $k = count($X);
    $Rbar = count($R) ? $mean($R) : null;
    $Xbar = count($X) ? $mean($X) : null;
    $Rp = $n ? max($pAvg) - min($pAvg) : null;
    $Xdif = $k ? max($X) - min($X) : null;

    $K1 = [2 => 0.8862, 3 => 0.5908][$r] ?? null;
    $K2 = [2 => 0.7071, 3 => 0.5231][$k] ?? null;
    $K3 = [2 => 0.7071, 3 => 0.5231, 4 => 0.4467, 5 => 0.4030, 6 => 0.3742, 7 => 0.3534, 8 => 0.3375, 9 => 0.3249, 10 => 0.3146][$n] ?? null;

    $EV = $n == 0 ? 0.0 : (($Rbar !== null && $K1 !== null) ? $Rbar * $K1 : null);
    $AV = ($Xdif !== null && $K2 !== null && $EV !== null && $n * $r > 0)
           ? sqrt(max(0, pow($Xdif * $K2, 2) - pow($EV, 2) / ($n * $r))) : null;
    $GRR = ($EV !== null && $AV !== null) ? sqrt($EV * $EV + $AV * $AV) : null;
    $PV = ($Rp !== null && $K3 !== null) ? $Rp * $K3 : null;
    $TV = ($GRR !== null && $PV !== null) ? sqrt($GRR * $GRR + $PV * $PV) : null;
    $pct = fn($x) => ($x !== null && $TV) ? 100 * $x / $TV : null;
    
    $pEV = $pct($EV);
    $pAV = $pct($AV);
    $pGRR = $pct($GRR);
    $pPV = $pct($PV);
    $ndc = ($PV !== null && $GRR) ? 1.41 * $PV / $GRR : null;
@endphp

<div style="padding: 20px; background: #fff; font-family: Helvetica, Arial, sans-serif; font-size: 11px;">
    {{-- Notes Alert --}}
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 12px; margin-bottom: 20px;">
        <div style="color: #856404; font-weight: bold; margin-bottom: 8px;">
            <i style="font-size: 14px; margin-right: 5px;">⚠️</i>Catatan Penting:
        </div>
        <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 10px;">
            <li>Tabel dengan latar belakang <span style="background: #ccffcc; padding: 2px 4px;">hijau</span> (Data Collection Sheet) harus <strong>terisi semua</strong></li>
            <li>Setiap cell harus berisi nilai numerik untuk Appraiser (A, B, C) × Trial × Part</li>
            <li>Jangan kosongkan cell manapun pada data collection sheet</li>
        </ul>
    </div>

    {{-- Header --}}
    <div style="border: 2px solid #000; padding: 15px; margin-bottom: 20px;">
        <h2 style="margin: 0 0 15px 0; text-align: center; font-size: 18px;">
            Gage Repeatability and Reproducibility (R&R) Report
        </h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 10px;">
            <div>
                <div style="margin-bottom: 5px;"><strong>Part No. & Name:</strong> {{ $h['h_part_no_name'] ?? '-' }}</div>
                <div style="margin-bottom: 5px;"><strong>Characteristics:</strong> {{ $h['h_characteristics'] ?? '-' }}</div>
                <div style="margin-bottom: 5px;">
                    <strong>Specifications:</strong> 
                    @if($specsFormatted)
                        <span style="background: #e3f2fd; padding: 2px 6px; border-radius: 3px; font-weight: bold;">
                            {{ $specsFormatted }}
                        </span>
                        <br>
                        <small style="color: #666;">({{ $specsWithRange }})</small>
                    @else
                        <span style="color: #d32f2f;">{{ $specs ?? '-' }} (Invalid format)</span>
                    @endif
                </div>
            </div>
            <div>
                <div style="margin-bottom: 5px;"><strong>Gage Name:</strong> {{ $h['h_gage_name'] ?? '-' }}</div>
                <div style="margin-bottom: 5px;"><strong>Date:</strong> {{ $h['h_date'] ?? '-' }}</div>
                <div style="margin-bottom: 5px;"><strong>Performed by:</strong> {{ $h['h_performed_by'] ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Data Collection Table --}}
    <div style="border: 1px solid #000; margin-bottom: 20px;">
        <h4 style="margin: 0; padding: 8px; background: #e8f4f8; border-bottom: 1px solid #000; text-align: center;">
            Data Collection Sheet
        </h4>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="border: 1px solid #000; padding: 5px; width: 80px;">Appraiser/<br>Trial #</th>
                        @for($p = 1; $p <= 10; $p++)
                            <th style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $p }}</th>
                        @endfor
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">AVERAGE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['A', 'B', 'C'] as $appraiser)
                        {{-- Trial rows --}}
                        <tr style="background: {{ $appraiser == 'B' ? '#e0f0e0' : ($appraiser == 'C' ? '#ffe0e0' : '#e0e0ff') }};">
                            <td style="border: 1px solid #000; padding: 5px; font-weight: bold;">{{ $appraiser }} - Trial 1</td>
                            @for($p = 1; $p <= 10; $p++)
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                    {{ $v["t_{$appraiser}_1_{$p}"] ?? '' }}
                                </td>
                            @endfor
                            <td style="border: 1px solid #000; padding: 3px; text-align: center;" rowspan="3"></td>
                        </tr>
                        <tr style="background: {{ $appraiser == 'B' ? '#e0f0e0' : ($appraiser == 'C' ? '#ffe0e0' : '#e0e0ff') }};">
                            <td style="border: 1px solid #000; padding: 5px; font-weight: bold;">{{ $appraiser }} - Trial 2</td>
                            @for($p = 1; $p <= 10; $p++)
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                    {{ $v["t_{$appraiser}_2_{$p}"] ?? '' }}
                                </td>
                            @endfor
                        </tr>
                        <tr style="background: {{ $appraiser == 'B' ? '#e0f0e0' : ($appraiser == 'C' ? '#ffe0e0' : '#e0e0ff') }};">
                            <td style="border: 1px solid #000; padding: 5px; font-weight: bold;">{{ $appraiser }} - Trial 3</td>
                            @for($p = 1; $p <= 10; $p++)
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                    {{ $v["t_{$appraiser}_3_{$p}"] ?? '' }}
                                </td>
                            @endfor
                        </tr>
                        {{-- Average row --}}
                        <tr style="background: #f5f5f5; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 5px;">Average</td>
                            @for($p = 1; $p <= 10; $p++)
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                    {{ $o["avg_{$appraiser}_{$p}"] ?? '' }}
                                </td>
                            @endfor
                            <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                {{ isset($X[$appraiser]) ? $fmt($X[$appraiser], 3) : '' }}
                            </td>
                        </tr>
                        {{-- Range row --}}
                        <tr style="background: #fff;">
                            <td style="border: 1px solid #000; padding: 5px;">Range</td>
                            @for($p = 1; $p <= 10; $p++)
                                <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                    {{ $o["rng_{$appraiser}_{$p}"] ?? '' }}
                                </td>
                            @endfor
                            <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                {{ isset($R[$appraiser]) ? $fmt($R[$appraiser], 3) : '' }}
                            </td>
                        </tr>
                    @endforeach
                    {{-- Part Average --}}
                    <tr style="background: #fff8dc; font-weight: bold;">
                        <td style="border: 1px solid #000; padding: 5px;">Part Average</td>
                        @for($p = 1; $p <= 10; $p++)
                            <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                {{ isset($pAvg[$p]) ? $fmt($pAvg[$p], 2) : '' }}
                            </td>
                        @endfor
                        <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                            {{ $Xbar !== null ? $fmt($Xbar, 3) : '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        {{-- Summary Statistics --}}
        <div style="padding: 10px; background: #f9f9f9; border-top: 1px solid #000; font-size: 10px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div><strong>R̄ (Average Range):</strong> {{ $Rbar !== null ? $fmt($Rbar, 4) : '-' }}</div>
                <div><strong>X̄<sub>DIFF</sub>:</strong> {{ $Xdif !== null ? $fmt($Xdif, 3) : '-' }}</div>
                <div><strong>R<sub>p</sub>:</strong> {{ $Rp !== null ? $fmt($Rp, 3) : '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Analysis Results --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        {{-- Left: Calculations --}}
        <div style="border: 1px solid #000; padding: 10px;">
            <h4 style="margin: 0 0 10px 0; padding: 8px; background: #e8f4f8; text-align: center; border-bottom: 1px solid #000;">
                Variation Components
            </h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="border: 1px solid #000; padding: 5px; text-align: left;">Component</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Value</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">% of TV</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><strong>Equipment Variation (EV)</strong></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $fmt($EV, 3) }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $fmt($pEV, 2) }}%</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><strong>Appraiser Variation (AV)</strong></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $fmt($AV, 3) }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $fmt($pAV, 2) }}%</td>
                    </tr>
                    <tr style="background: #fffacd;">
                        <td style="border: 1px solid #000; padding: 5px;"><strong>Gage R&R (GRR)</strong></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ $fmt($GRR, 3) }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ $fmt($pGRR, 2) }}%</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><strong>Part Variation (PV)</strong></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $fmt($PV, 3) }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $fmt($pPV, 2) }}%</td>
                    </tr>
                    <tr style="background: #e8f4f8;">
                        <td style="border: 1px solid #000; padding: 5px;"><strong>Total Variation (TV)</strong></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">{{ $fmt($TV, 3) }}</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">100.00%</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 10px; padding: 8px; background: #f0f8ff; border: 1px solid #4682b4;">
                <strong>Number of Distinct Categories (ndc):</strong> {{ $fmt($ndc, 2) }}
            </div>
        </div>

        {{-- Right: Interpretation --}}
        <div style="border: 1px solid #000; padding: 10px;">
            <h4 style="margin: 0 0 10px 0; padding: 8px; background: #e8f4f8; text-align: center; border-bottom: 1px solid #000;">
                Interpretation Guidelines
            </h4>
            
            <div style="margin-bottom: 15px;">
                <strong style="display: block; margin-bottom: 5px;">Gage R&R Assessment:</strong>
                @if($pGRR !== null)
                    @if($pGRR < 10)
                        <div style="padding: 8px; background: #d4edda; border: 1px solid #28a745; border-radius: 3px;">
                            <strong style="color: #155724;">✓ ACCEPTABLE</strong><br>
                            <span style="font-size: 10px;">GRR < 10% - Measurement system is acceptable</span>
                        </div>
                    @elseif($pGRR < 30)
                        <div style="padding: 8px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 3px;">
                            <strong style="color: #856404;">⚠ MARGINAL</strong><br>
                            <span style="font-size: 10px;">10% ≤ GRR < 30% - May be acceptable based on application</span>
                        </div>
                    @else
                        <div style="padding: 8px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 3px;">
                            <strong style="color: #721c24;">✗ NOT ACCEPTABLE</strong><br>
                            <span style="font-size: 10px;">GRR ≥ 30% - Measurement system needs improvement</span>
                        </div>
                    @endif
                @else
                    <div style="padding: 8px; background: #e9ecef; border: 1px solid #6c757d; border-radius: 3px;">
                        <span style="font-size: 10px;">Insufficient data for assessment</span>
                    </div>
                @endif
            </div>

            <div style="margin-bottom: 15px;">
                <strong style="display: block; margin-bottom: 5px;">Number of Distinct Categories (ndc):</strong>
                @if($ndc !== null)
                    @if($ndc >= 5)
                        <div style="padding: 8px; background: #d4edda; border: 1px solid #28a745; border-radius: 3px;">
                            <strong style="color: #155724;">✓ ACCEPTABLE</strong><br>
                            <span style="font-size: 10px;">ndc ≥ 5 - Adequate resolution</span>
                        </div>
                    @elseif($ndc >= 2)
                        <div style="padding: 8px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 3px;">
                            <strong style="color: #856404;">⚠ MARGINAL</strong><br>
                            <span style="font-size: 10px;">2 ≤ ndc < 5 - Limited resolution</span>
                        </div>
                    @else
                        <div style="padding: 8px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 3px;">
                            <strong style="color: #721c24;">✗ NOT ACCEPTABLE</strong><br>
                            <span style="font-size: 10px;">ndc < 2 - Insufficient resolution</span>
                        </div>
                    @endif
                @else
                    <div style="padding: 8px; background: #e9ecef; border: 1px solid #6c757d; border-radius: 3px;">
                        <span style="font-size: 10px;">Insufficient data for assessment</span>
                    </div>
                @endif
            </div>

            <div style="padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 3px; font-size: 10px;">
                <strong>Notes:</strong>
                <ul style="margin: 5px 0 0 20px; padding: 0;">
                    <li>GRR < 10%: Excellent measurement system</li>
                    <li>GRR 10-30%: May be acceptable depending on importance</li>
                    <li>GRR > 30%: Unacceptable, requires improvement</li>
                    <li>ndc ≥ 5: System can distinguish between parts</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Formulas Reference (collapsed by default) --}}
    <details style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background: #fafafa;">
        <summary style="cursor: pointer; font-weight: bold; padding: 5px;">
            <i class="ti ti-math"></i> Show Calculation Formulas
        </summary>
        <div style="margin-top: 10px; font-size: 10px; font-family: 'Courier New', monospace;">
            <div style="margin-bottom: 5px;"><strong>EV</strong> = R̄ × K₁</div>
            <div style="margin-bottom: 5px;"><strong>AV</strong> = √[(X̄<sub>DIFF</sub> × K₂)² - (EV² / (n × r))]</div>
            <div style="margin-bottom: 5px;"><strong>GRR</strong> = √(EV² + AV²)</div>
            <div style="margin-bottom: 5px;"><strong>PV</strong> = R<sub>p</sub> × K₃</div>
            <div style="margin-bottom: 5px;"><strong>TV</strong> = √(GRR² + PV²)</div>
            <div style="margin-bottom: 5px;"><strong>ndc</strong> = 1.41 × (PV / GRR)</div>
            <div style="margin-top: 10px; font-size: 9px;">
                <em>K₁, K₂, K₃ are constants based on number of trials, appraisers, and parts</em>
            </div>
        </div>
    </details>
</div>
