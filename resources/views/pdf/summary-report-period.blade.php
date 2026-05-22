<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 14mm 14mm 22mm 14mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8pt;
            color: #212529;
            background: #fff;
        }

        .hdr-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }

        .hdr-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }

        .hdr-logo {
            width: 70px;
        }

        .hdr-logo img {
            width: 62px;
            height: auto;
        }

        .hdr-title {
            font-size: 15pt;
            font-weight: bold;
            color: #212529;
            line-height: 1.1;
        }

        .hdr-sub {
            font-size: 9pt;
            font-style: italic;
            color: #495057;
            margin-top: 1px;
        }

        .hdr-meta {
            font-size: 8.5pt;
            color: #495057;
            margin-top: 1px;
        }

        .hdr-gen {
            text-align: right;
            font-size: 7.5pt;
            color: #6c757d;
            vertical-align: bottom;
        }

        .hdr-rule {
            border: none;
            border-top: 1.5px solid #343a40;
            margin: 3px 0 8px;
        }

        .stat-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .stat-table th {
            background: #343a40;
            color: #fff;
            font-size: 6.5pt;
            text-align: center;
            padding: 3px 2px;
            border: 0.5px solid #555;
            word-break: break-word;
        }

        .stat-table th.th-empty {
            text-align: left;
        }

        .stat-table td {
            font-size: 6.5pt;
            text-align: center;
            padding: 2.5px 2px;
            border: 0.5px solid #d0d0d0;
        }

        .td-rowlabel {
            text-align: left;
            font-weight: bold;
            background: #fff;
            word-break: break-word;
        }

        .td-sub {
            text-align: left;
            font-style: italic;
            font-weight: normal;
            color: #555;
        }

        .td-sep-max {
            color: #b40000;
            font-weight: bold;
            font-size: 6pt;
            background: #fff;
            padding: 1.5px 2px;
            border-top: 0.5px solid #d0d0d0;
        }

        .td-sep-min {
            color: #00822c;
            font-weight: bold;
            font-size: 6pt;
            background: #fff;
            padding: 1.5px 2px;
            border-top: 0.5px solid #d0d0d0;
        }

        .td-num {
            font-family: DejaVu Sans Mono, monospace;
        }

        /* Daily Average Table (Page 2) */
        .daily-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .daily-table th {
            background: #343a40;
            color: #fff;
            font-size: 6.5pt;
            text-align: center;
            padding: 3px 2px;
            border: 0.5px solid #555;
        }

        .daily-table td {
            font-size: 6.5pt;
            text-align: center;
            padding: 2px 2px;
            border: 0.5px solid #d0d0d0;
        }

        .td-date {
            text-align: center;
            font-weight: bold;
            background: #f5f5f5;
            font-size: 6pt;
        }

        /* Daily Count Summary (Page 3) */
        .dc-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .dc-table th {
            background: #343a40;
            color: #fff;
            font-size: 6pt;
            text-align: center;
            padding: 2.5px 2px;
            border: 0.5px solid #555;
            word-break: break-word;
        }

        .dc-table td {
            font-size: 6pt;
            text-align: center;
            padding: 2px 2px;
            border: 0.5px solid #d0d0d0;
        }

        .dc-date {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 6pt;
            text-align: center;
        }

        .dc-none  { background: #cf7979; color: #fff; }
        .dc-full  { background: #d4edda; color: #155724; font-weight: bold; }
        .dc-part  { background: #fff3cd; color: #856404; }
        .dc-total { background: #f1f3f5; font-weight: bold; }
        .dc-pct-g { background: #d4edda; font-weight: bold; color: #155724; }
        .dc-pct-y { background: #fff3cd; font-weight: bold; color: #856404; }
        .dc-pct-r { background: #ffd7da; font-weight: bold; color: #cf7979; }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .chart-table td {
            border: none;
            padding: 0 3px;
            vertical-align: top;
            width: 50%;
        }

        .chart-box {
            border: 0.5px solid #e0e0e0;
            border-radius: 2px;
            padding: 4px;
            margin-bottom: 4px;
        }

        .chart-box svg {
            width: 100%;
            height: auto;
            display: block;
        }

        .chart-annotation {
            font-size: 6pt;
            color: #505050;
            margin-top: 1px;
            line-height: 1.3;
        }

        .page-break {
            page-break-before: always;
        }

        /* footer rendered via canvas in SummaryReportService */
    </style>
</head>

<body style="padding:5px 50px;">

    @php
        $logoPath = public_path('assets/img/HasSolution.png');
        $genTime  = now()->format('Y-m-d H:i:s');
        $pCount   = count($stats);

        $renderHeader = function (string $subtitle) use (
            $logoPath,
            $device_id,
            $device_category,
            $date_range,
            $genTime,
        ): string {
            $logoHtml = file_exists($logoPath)
                ? '<img src="' . $logoPath . '" style="width:150px;height:auto; margin:5px" >'
                : '';
            $did  = e($device_id);
            $dcat = e($device_category);
            $ds   = e($date_range['start']);
            $de   = e($date_range['end']);
            $sub  = e($subtitle);
            $gen  = e($genTime);
            return "
        <table class='hdr-table'>
            <tr>
                <td class='hdr-logo'>{$logoHtml}</td>
                <td>
                    <div class='hdr-title'>Automated Report</div>
                    <div class='hdr-sub'>{$sub}</div>
                    <div class='hdr-meta'>Device: {$did} &nbsp;|&nbsp; Category: {$dcat}</div>
                    <div class='hdr-meta'>Period: {$ds} &nbsp; to &nbsp; {$de}</div>
                </td>
                <td class='hdr-gen'>Generated: {$gen}</td>
            </tr>
        </table>
        <hr class='hdr-rule'>
        ";
        };

        $buildChart = function (array $data, string $color, string $title, array $labels = []): string {
            $svgW = 740;
            $svgH = 300;
            $padL = 34;
            $padR = 8;
            $padT = 20;
            $padB = 26;
            $cw   = $svgW - $padL - $padR;
            $ch   = $svgH - $padT - $padB;

            $n = count($data);
            if ($n === 0) {
                return '';
            }

            $nonNull = array_values(array_filter($data, fn($v) => $v !== null));
            $vMin    = count($nonNull) ? min($nonNull) : 0;
            $vMax    = count($nonNull) ? max($nonNull) : 1;
            $vRng    = $vMax - $vMin;
            if ($vRng == 0) {
                $vMin -= 1;
                $vMax += 1;
                $vRng  = 2;
            }

            $stepX = $n > 1 ? $cw / ($n - 1) : $cw;
            $toY   = fn($v) => round($padT + $ch - (($v - $vMin) / $vRng) * $ch, 1);
            $toX   = fn($i) => round($padL + $i * $stepX, 1);

            $segments = [];
            $cur      = [];
            for ($i = 0; $i < $n; $i++) {
                if ($data[$i] !== null) {
                    $cur[] = [$toX($i), $toY($data[$i])];
                } else {
                    if (!empty($cur)) {
                        $segments[] = $cur;
                    }
                    $cur = [];
                }
            }
            if (!empty($cur)) {
                $segments[] = $cur;
            }

            $dotPoints = [];
            for ($i = 0; $i < $n; $i++) {
                if ($data[$i] !== null) {
                    $dotPoints[] = [$toX($i), $toY($data[$i])];
                }
            }

            $svg  = "<svg xmlns='http://www.w3.org/2000/svg' width='{$svgW}' height='{$svgH}'>";
            $svg .= "<rect width='{$svgW}' height='{$svgH}' fill='white'/>";

            $cx   = $padL + $cw / 2;
            $svg .=
                "<text x='{$cx}' y='14' text-anchor='middle' font-size='8' font-weight='bold' fill='#343a40'>" .
                htmlspecialchars($title) .
                '</text>';

            for ($g = 0; $g <= 4; $g++) {
                $gy  = $padT + ($g / 4) * $ch;
                $gv  = $vMax - ($g / 4) * $vRng;
                $lbl = abs($gv) < 0.005 ? '0' : (abs($gv) >= 1000 ? round($gv, 0) : round($gv, 1));
                $svg .=
                    "<line x1='{$padL}' y1='{$gy}' x2='" .
                    ($padL + $cw) .
                    "' y2='{$gy}' stroke='#e8e8e8' stroke-width='0.5'/>";
                $svg .=
                    "<text x='" .
                    ($padL - 3) .
                    "' y='" .
                    ($gy + 3) .
                    "' text-anchor='end' font-size='5.5' fill='#888'>{$lbl}</text>";
            }

            $labelInterval = 1;
            if ($n > 8) {
                $labelInterval = (int) ceil($n / 6);
            }

            for ($i = 0; $i < $n; $i++) {
                if ($i % $labelInterval == 0 || $i == $n - 1) {
                    $lx  = $toX($i);
                    $ly  = $padT + $ch + 12;
                    $lbl = $labels[$i] ?? $i;
                    $svg .=
                        "<line x1='{$lx}' y1='" .
                        ($padT + $ch) .
                        "' x2='{$lx}' y2='" .
                        ($padT + $ch + 3) .
                        "' stroke='#ccc' stroke-width='0.5'/>";
                    $svg .= "<text x='{$lx}' y='{$ly}' text-anchor='middle' font-size='5' fill='#888'>{$lbl}</text>";
                }
            }

            $axisB = $padT + $ch;
            $svg .= "<line x1='{$padL}' y1='{$padT}' x2='{$padL}' y2='{$axisB}' stroke='#bbb' stroke-width='0.7'/>";
            $svg .=
                "<line x1='{$padL}' y1='{$axisB}' x2='" .
                ($padL + $cw) .
                "' y2='{$axisB}' stroke='#bbb' stroke-width='0.7'/>";

            foreach ($segments as $seg) {
                if (count($seg) === 1) {
                    [$px, $py] = $seg[0];
                    $svg .= "<circle cx='{$px}' cy='{$py}' r='2.5' fill='{$color}'/>";
                } else {
                    $d    = 'M ' . implode(' L ', array_map(fn($p) => $p[0] . ' ' . $p[1], $seg));
                    $svg .= "<path fill='none' stroke='{$color}' stroke-width='1.4' d='{$d}'/>";
                }
            }
            foreach ($dotPoints as [$px, $py]) {
                $svg .= "<circle cx='{$px}' cy='{$py}' r='2' fill='{$color}'/>";
            }

            $svg .= '</svg>';
            return '<img src="data:image/svg+xml;base64,' .
                base64_encode($svg) .
                '" width="' .
                $svgW .
                '" height="' .
                $svgH .
                '" style="display:block;" />';
        };

        $chartColors = [
            '#0d6efd', '#dc3545', '#198754', '#fd7e14',
            '#6f42c1', '#20c997', '#0dcaf0', '#ffc107',
            '#d63384', '#6c757d', '#e83e8c', '#17a2b8',
            '#2980b9', '#27ae60',
        ];
    @endphp

    {{-- PAGE 1: Statistical Overview --}}
    {!! $renderHeader('Statistical Overview') !!}

    {{-- Map image --}}
    @if (!empty($map_image) || (!empty($latitude) && !empty($longitude)))
    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <tr>
            @if (!empty($map_image))
            <td style="vertical-align:top; padding-right:8px;">
                <img src="{{ $map_image }}"
                     style="width:100%; max-width:1264px; height:500px; border:0.5px solid #dee2e6; border-radius:2px;" />
            </td>
            @endif
            <td style="vertical-align:top; width:350px; font-size:10pt; color:#495057;
                        padding:4px 0; border-left:2px solid #343a40; padding-left:8px;">
                <div style="font-weight:bold; font-size:10pt; color:#343a40; margin-bottom:3px;">
                    {{ $device_category }} &mdash; {{ $device_id }}
                </div>
                @if (!empty($location))
                <div style="margin-bottom:2px;"><strong>Location:</strong> {{ $location }}</div>
                @endif
                @if (!empty($district))
                <div style="margin-bottom:2px;"><strong>District:</strong> {{ $district }}</div>
                @endif
                @if (!empty($latitude) && !empty($longitude))
                <div style="margin-top:4px; font-size:8.5pt; color:#6c757d; line-height:1.5;">
                    Latitude: {{ number_format((float)$latitude, 6) }}<br>
                    Longitude: {{ number_format((float)$longitude, 6) }}
                </div>
                @endif
                @if (empty($map_image))
                <div style="margin-top:6px; font-size:8pt; color:#adb5bd;">(Map unavailable)</div>
                @endif
            </td>
        </tr>
    </table>
    @endif

    <table class="stat-table">
        <thead>
            <tr>
                <th class="th-empty" style="width:44px;"></th>
                @foreach ($stats as $s)
                    <th>{{ $s['parameter_label'] }}@if ($s['parameter_unit'])
                            <br />({{ $s['parameter_unit'] }})
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="td-rowlabel">Average</td>
                @foreach ($stats as $s)
                    <td class="td-num">{{ $s['average'] !== null ? number_format($s['average'], 2) : '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="td-sep-max" colspan="{{ $pCount + 1 }}">&#9650;&nbsp; MAXIMUM</td>
            </tr>
            <tr>
                <td class="td-rowlabel">Value</td>
                @foreach ($stats as $s)
                    <td class="td-num">{{ $s['max'] !== null ? number_format($s['max'], 2) : '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="td-sub">Time</td>
                @foreach ($stats as $s)
                    <td style="font-size:6pt;font-style:italic;">{{ $s['max_time'] ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="td-sub">Date</td>
                @foreach ($stats as $s)
                    <td style="font-size:6pt;font-style:italic;">{{ $s['max_date'] ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="td-sep-min" colspan="{{ $pCount + 1 }}">&#9660;&nbsp; MINIMUM</td>
            </tr>
            <tr>
                <td class="td-rowlabel">Value</td>
                @foreach ($stats as $s)
                    <td class="td-num">{{ $s['min'] !== null ? number_format($s['min'], 2) : '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="td-sub">Time</td>
                @foreach ($stats as $s)
                    <td style="font-size:6pt;font-style:italic;">{{ $s['min_time'] ?? '-' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="td-sub">Date</td>
                @foreach ($stats as $s)
                    <td style="font-size:6pt;font-style:italic;">{{ $s['min_date'] ?? '-' }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

    {{-- PAGE 2: Daily Average Table --}}
    <div class="page-break"></div>
    {!! $renderHeader('Daily Average') !!}
    <table class="daily-table">
        <thead>
            <tr>
                <th style="text-align:center;width:22mm;">Date</th>
                @foreach ($stats as $s)
                    <th>{{ $s['parameter_label'] }}@if ($s['parameter_unit'])
                            <br />({{ $s['parameter_unit'] }})
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($daily_table as $slot)
                <tr>
                    <td class="td-date">{{ $slot['date'] }}</td>
                    @foreach ($stats as $s)
                        @php $v = $slot['values'][$s['parameter_name']] ?? null; @endphp
                        <td class="td-num">{{ $v !== null ? number_format($v, 2) : '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PAGE 3: Daily Count Summary --}}
    @php
        $dcDates   = $daily_count['dates']     ?? [];
        $dcByDate  = $daily_count['by_date']   ?? [];
        $dcDailyMax = (int) ($daily_count['daily_max'] ?? 0);
        $dcColspan = $pCount + 3; // Date + params + Total + %
    @endphp
    <div class="page-break"></div>
    {!! $renderHeader('Daily Count Summary') !!}

    <table class="dc-table">
        <thead>
            <tr>
                <th style="width:20mm;">Date</th>
                @foreach ($stats as $s)
                    <th>{{ $s['parameter_label'] }}@if ($s['parameter_unit'])
                            <br />({{ $s['parameter_unit'] }})
                        @endif
                    </th>
                @endforeach
                <th style="width:14mm;">Total<br>Data</th>
                <th style="width:10mm;">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dcDates as $date)
                @php
                    $row       = $dcByDate[$date] ?? [];
                    $parts     = explode('-', $date);
                    $dispDate  = count($parts) === 3
                        ? "{$parts[2]}-{$parts[1]}-{$parts[0]}"
                        : $date;
                    $totalData = 0;
                    $maxData   = $pCount * $dcDailyMax;  // expected total across all params
                @endphp
                <tr>
                    <td class="dc-date">{{ $dispDate }}</td>
                    @foreach ($stats as $s)
                        @php
                            $cnt = $row[$s['parameter_name']] ?? null;
                            $totalData += (int) ($cnt ?? 0);
                        @endphp
                        @if ($cnt === null || $cnt === 0)
                            <td class="dc-none">✕</td>
                        @elseif ($dcDailyMax > 0 && $cnt >= $dcDailyMax)
                            <td class="dc-full">{{ $cnt }}</td>
                        @else
                            <td class="dc-part">{{ $cnt }}</td>
                        @endif
                    @endforeach
                    @php
                        $pctVal   = $maxData > 0 ? round($totalData / $maxData * 100, 1) : 0;
                        $pctClass = $pctVal >= 84 ? 'dc-pct-g'
                                  : ($pctVal >= 50 ? 'dc-pct-y' : 'dc-pct-r');
                    @endphp
                    <td class="dc-total">{{ $totalData }}</td>
                    <td class="{{ $pctClass }}">{{ number_format($pctVal, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PAGES 4+: Graphic Overview (6 charts per page, 2 columns × 3 rows) --}}
    @php
        $chartIndex  = 0;
        $chartLabels = $daily_chart_labels ?? [];
    @endphp
    @foreach ($stats as $idx => $s)
        @php
            $paramName  = $s['parameter_name'];
            $unit       = $s['parameter_unit'] ?? '';
            $chartData  = $daily_charts[$paramName] ?? [];
            $color      = $chartColors[$idx % count($chartColors)];
            $chartTitle = $s['parameter_label'] . ($unit ? '  (' . $unit . ')' : '');
            $isNewPage  = $chartIndex % 6 === 0;
            $isLeft     = $chartIndex % 2 === 0;
            $chartIndex++;

            $fmtV = fn($v) => $v !== null ? number_format($v, 2) : '-';
            $note =
                'Max: ' . $fmtV($s['max']) .
                '  (' . ($s['max_time'] ?? '-') . ' ' . ($s['max_date'] ?? '-') . ')' .
                '      Min: ' . $fmtV($s['min']) .
                '  (' . ($s['min_time'] ?? '-') . ' ' . ($s['min_date'] ?? '-') . ')' .
                '      Avg: ' . $fmtV($s['average']) . ($unit ? ' ' . $unit : '');
        @endphp

        @if ($isNewPage)
            <div class="page-break"></div>
            {!! $renderHeader('Graphic Overview') !!}
            <table class="chart-table">
            <tbody>
        @endif

        @if ($isLeft)
                <tr>
        @endif

        <td>
            <div class="chart-box" style="margin:5px">{!! $buildChart($chartData, $color, $chartTitle, $chartLabels) !!}</div>
            <div class="chart-annotation">{{ $note }}</div>
        </td>

        @if (!$isLeft || $loop->last)
            @if ($loop->last && $isLeft)
                <td></td>
            @endif
                </tr>
            @if ($chartIndex % 6 === 0 || $loop->last)
            </tbody>
            </table>
            @endif
        @endif
    @endforeach

</body>

</html>
