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

        .hourly-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .hourly-table th {
            background: #343a40;
            color: #fff;
            font-size: 6.5pt;
            text-align: center;
            padding: 3px 2px;
            border: 0.5px solid #555;
        }

        .hourly-table td {
            font-size: 6.5pt;
            text-align: center;
            padding: 2px 2px;
            border: 0.5px solid #d0d0d0;
        }

        .td-hour {
            text-align: center;
            font-weight: bold;
            background: #f5f5f5;
            font-size: 6pt;
        }

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

        /* ── Hourly Count Summary (PAGE 3) ───────────────────── */
        .ct-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .ct-table th {
            background: #343a40;
            color: #fff;
            font-size: 5.5pt;
            text-align: center;
            padding: 2.5px 1px;
            border: 0.5px solid #555;
        }

        .ct-table td {
            font-size: 5.5pt;
            text-align: center;
            padding: 2px 1px;
            border: 0.5px solid #d0d0d0;
        }

        .ct-sep {
            background: #343a40;
            color: #fff;
            font-size: 6pt;
            font-weight: bold;
            text-align: left;
            padding: 2.5px 4px;
            border: 0.5px solid #555;
        }

        .ct-date {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 5.5pt;
            text-align: center;
        }

        .ct-none {
            background: #cf7979;
            color: #fff;
        }

        .ct-full {
            background: #d4edda;
            color: #155724;
            font-weight: bold;
        }

        .ct-part {
            background: #fff3cd;
            color: #856404;
        }

        .ct-total {
            background: #f1f3f5;
            font-weight: bold;
        }

        .ct-pct-g {
            background: #d4edda;
            font-weight: bold;
            color: #155724;
        }

        .ct-pct-y {
            background: #fff3cd;
            font-weight: bold;
            color: #856404;
        }

        .ct-pct-r {
            background: #ffd7da;
            font-weight: bold;
            color: #cf7979;
        }

        /* footer rendered via canvas in SummaryReportService */
    </style>
</head>

<body style="padding:5px 50px;">

    @php
        $logoPath = public_path('assets/img/HasSolution.png');
        $genTime = now()->format('Y-m-d H:i:s');
        $pCount = count($stats);

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
            $did = e($device_id);
            $dcat = e($device_category);
            $ds = e($date_range['start']);
            $de = e($date_range['end']);
            $sub = e($subtitle);
            $gen = e($genTime);
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
            $svgH = 320;

            $padL = 42;
            $padR = 16;
            $padT = 28;
            $padB = 42;

            $cw = $svgW - $padL - $padR;
            $ch = $svgH - $padT - $padB;

            $n = count($data);

            if ($n === 0) {
                return '';
            }

            $nonNull = array_values(array_filter($data, fn($v) => $v !== null));

            $vMin = count($nonNull) ? min($nonNull) : 0;
            $vMax = count($nonNull) ? max($nonNull) : 1;

            $padding = ($vMax - $vMin) * 0.12;

            if ($padding <= 0) {
                $padding = 1;
            }

            $vMin -= $padding;
            $vMax += $padding;

            $vRng = $vMax - $vMin;

            $stepX = $n > 1 ? $cw / ($n - 1) : $cw;

            $toY = fn($v) => round($padT + $ch - (($v - $vMin) / $vRng) * $ch, 1);

            $toX = fn($i) => round($padL + $i * $stepX, 1);

            $points = [];

            for ($i = 0; $i < $n; $i++) {
                if ($data[$i] !== null) {
                    $points[] = [
                        'x' => $toX($i),
                        'y' => $toY($data[$i]),
                        'value' => $data[$i],
                    ];
                }
            }

            if (empty($points)) {
                return '';
            }

            /*
    |--------------------------------------------------------------------------
    | Smooth Path Generator
    |--------------------------------------------------------------------------
    */
            $linePath = '';

            foreach ($points as $i => $p) {
                if ($i == 0) {
                    $linePath .= "M {$p['x']} {$p['y']} ";
                    continue;
                }

                $prev = $points[$i - 1];

                $cx = ($prev['x'] + $p['x']) / 2;

                $linePath .= "Q {$cx} {$prev['y']} {$p['x']} {$p['y']} ";
            }

            /*
    |--------------------------------------------------------------------------
    | Area Fill
    |--------------------------------------------------------------------------
    */
            $first = $points[0];
            $last = $points[count($points) - 1];

            $areaPath = $linePath . "L {$last['x']} " . ($padT + $ch) . " L {$first['x']} " . ($padT + $ch) . ' Z';

            $svg = "
    <svg xmlns='http://www.w3.org/2000/svg'
         width='{$svgW}'
         height='{$svgH}'
         viewBox='0 0 {$svgW} {$svgH}'>

        <defs>

            <linearGradient id='grad-{$color}'
                            x1='0'
                            y1='0'
                            x2='0'
                            y2='1'>

                <stop offset='0%'
                      stop-color='{$color}'
                      stop-opacity='0.30'/>

                <stop offset='100%'
                      stop-color='{$color}'
                      stop-opacity='0'/>

            </linearGradient>

            <filter id='shadow'>
                <feDropShadow dx='0'
                              dy='1'
                              stdDeviation='2'
                              flood-opacity='0.15'/>
            </filter>

        </defs>

        <rect width='100%'
              height='100%'
              fill='white'/>
    ";

            /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */
            $svg .=
                "
        <text x='" .
                $svgW / 2 .
                "'
              y='18'
              text-anchor='middle'
              font-size='11'
              font-weight='600'
              fill='#1f2937'>
            " .
                htmlspecialchars($title) .
                "
        </text>
    ";

            /*
    |--------------------------------------------------------------------------
    | Grid Y
    |--------------------------------------------------------------------------
    */
            for ($g = 0; $g <= 5; $g++) {
                $gy = $padT + ($g / 5) * $ch;

                $gv = $vMax - ($g / 5) * $vRng;

                $lbl = round($gv, 1);

                $svg .=
                    "
            <line x1='{$padL}'
                  y1='{$gy}'
                  x2='" .
                    ($padL + $cw) .
                    "'
                  y2='{$gy}'
                  stroke='#eef2f7'
                  stroke-width='1'/>

            <text x='" .
                    ($padL - 6) .
                    "'
                  y='" .
                    ($gy + 4) .
                    "'
                  text-anchor='end'
                  font-size='9'
                  fill='#94a3b8'>
                {$lbl}
            </text>
        ";
            }

            /*
    |--------------------------------------------------------------------------
    | X Labels
    |--------------------------------------------------------------------------
    */
            $labelInterval = max(1, ceil($n / 8));

            $rotate = $n > 10;

            for ($i = 0; $i < $n; $i++) {
                if ($i % $labelInterval !== 0 && $i != $n - 1) {
                    continue;
                }

                $x = $toX($i);

                $label = htmlspecialchars($labels[$i] ?? $i);

                $svg .=
                    "
            <line x1='{$x}'
                  y1='" .
                    ($padT + $ch) .
                    "'
                  x2='{$x}'
                  y2='" .
                    ($padT + $ch + 4) .
                    "'
                  stroke='#cbd5e1'
                  stroke-width='1'/>
        ";

                if ($rotate) {
                    $svg .=
                        "
                <text x='{$x}'
                      y='" .
                        ($padT + $ch + 16) .
                        "'
                      transform='rotate(45 {$x}," .
                        ($padT + $ch + 16) .
                        ")'
                      text-anchor='start'
                      font-size='8'
                      fill='#94a3b8'>
                    {$label}
                </text>
            ";
                } else {
                    $svg .=
                        "
                <text x='{$x}'
                      y='" .
                        ($padT + $ch + 16) .
                        "'
                      text-anchor='middle'
                      font-size='9'
                      fill='#94a3b8'>
                    {$label}
                </text>
            ";
                }
            }

            /*
    |--------------------------------------------------------------------------
    | Axis
    |--------------------------------------------------------------------------
    */
            $svg .=
                "
        <line x1='{$padL}'
              y1='{$padT}'
              x2='{$padL}'
              y2='" .
                ($padT + $ch) .
                "'
              stroke='#cbd5e1'/>

        <line x1='{$padL}'
              y1='" .
                ($padT + $ch) .
                "'
              x2='" .
                ($padL + $cw) .
                "'
              y2='" .
                ($padT + $ch) .
                "'
              stroke='#cbd5e1'/>
    ";

            /*
    |--------------------------------------------------------------------------
    | Area Fill
    |--------------------------------------------------------------------------
    */
            $svg .= "
        <path d='{$areaPath}'
              fill='url(#grad-{$color})'/>
    ";

            /*
    |--------------------------------------------------------------------------
    | Main Line
    |--------------------------------------------------------------------------
    */
            $svg .= "
        <path d='{$linePath}'
              fill='none'
              stroke='{$color}'
              stroke-width='3'
              stroke-linecap='round'
              stroke-linejoin='round'
              filter='url(#shadow)'/>
    ";

            /*
    |--------------------------------------------------------------------------
    | Dots
    |--------------------------------------------------------------------------
    */
            foreach ($points as $p) {
                $svg .= "
            <circle cx='{$p['x']}'
                    cy='{$p['y']}'
                    r='4'
                    fill='white'
                    stroke='{$color}'
                    stroke-width='2'/>

            <circle cx='{$p['x']}'
                    cy='{$p['y']}'
                    r='2'
                    fill='{$color}'/>
        ";
            }

            $svg .= '</svg>';

            return '<img src="data:image/svg+xml;base64,' .
                base64_encode($svg) .
                '" width="' .
                $svgW .
                '" height="' .
                $svgH .
                '" style="display:block;width:100%;max-width:' .
                $svgW .
                'px;" />';
        };

        $chartColors = [
            '#0d6efd',
            '#dc3545',
            '#198754',
            '#fd7e14',
            '#6f42c1',
            '#20c997',
            '#0dcaf0',
            '#ffc107',
            '#d63384',
            '#6c757d',
            '#e83e8c',
            '#17a2b8',
            '#2980b9',
            '#27ae60',
        ];
    @endphp

    {{-- PAGE 1: Statistical Overview --}}
    {!! $renderHeader('Statistical Overview') !!}


    {{-- Maps images --}}
    @if (!empty($map_image) || (!empty($latitude) && !empty($longitude)))
        <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
            <tr>
                @if (!empty($map_image))
                    <td style="vertical-align:top; padding-right:8px;">
                        <img src="{{ $map_image }}"
                            style="width:100%; max-width:1264px; height:500px;
                                border:0.5px solid #ced4da; border-radius:2px;" />
                    </td>
                @endif
                <td
                    style="vertical-align:top; width:350px; font-size:10pt; color:#495057;
                            padding:4px 0; border-left:2px solid #343a40; padding-left:8px;">
                    <div style="font-weight:bold; font-size:10pt; color:#343a40; margin-bottom:3px;">
                        Device Location
                    </div>
                    @if (!empty($location))
                        <div style="margin-bottom:2px;"><strong>Location:</strong> {{ $location }}</div>
                    @endif
                    @if (!empty($district))
                        <div style="margin-bottom:2px;"><strong>District:</strong> {{ $district }}</div>
                    @endif
                    @if (!empty($latitude) && !empty($longitude))
                        <div style="margin-top:4px; font-size:8.5pt; color:#6c757d; line-height:1.5;">
                            Latitude:&nbsp; {{ number_format((float) $latitude, 6) }}<br>
                            Longitude: {{ number_format((float) $longitude, 6) }}
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

    {{-- PAGE 2: Hourly Average Table --}}

    <div class="page-break"></div>
    {!! $renderHeader('Hourly Average') !!}
    <table class="hourly-table">
        <thead>
            <tr>
                <th style="text-align:center;width:55px;">Date / Hour</th>
                @foreach ($stats as $s)
                    <th>{{ $s['parameter_label'] }}@if ($s['parameter_unit'])
                            <br />({{ $s['parameter_unit'] }})
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($hourly_table as $slot)
                <tr>
                    <td class="td-hour">{{ $slot['datetime'] }}</td>
                    @foreach ($stats as $s)
                        @php $v = $slot['values'][$s['parameter_name']] ?? null; @endphp
                        <td class="td-num">{{ $v !== null ? number_format($v, 2) : '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>


    {{-- PAGE 3 : Hourly Count Summary --}}

    @php
        $hours24 = range(0, 23);
    @endphp
    <div class="page-break"></div>
    {!! $renderHeader('Hourly Count Summary') !!}

    @foreach ($hourly_count as $paramName => $cData)
        @php
            $paramLabel = $cData['param_label'];
            $paramUnit = $cData['param_unit'];
            $hourlyMax = (int) $cData['hourly_data'];
            $dates = $cData['dates'];
            $byDate = $cData['by_date'];
        @endphp

        <table class="ct-table" style="margin-bottom:6px;">
            {{-- Parameter separator header --}}
            <thead>
                <tr>
                    <td class="ct-sep" colspan="27">
                        {{ $paramLabel }}{{ $paramUnit ? ' (' . $paramUnit . ')' : '' }}
                        {{ $hourlyMax > 0 ? '  —  expected ' . $hourlyMax . ' data/hr' : '' }}
                    </td>
                </tr>
                <tr>
                    <th style="width:18mm;">Date</th>
                    @foreach ($hours24 as $h)
                        <th>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</th>
                    @endforeach
                    <th style="width:12mm;">Total<br>Data</th>
                    <th style="width:10mm;">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dates as $date)
                    @php
                        $hourData = $byDate[$date] ?? array_fill(0, 24, null);
                        $parts = explode('-', $date);
                        $dispDate = count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : $date;
                        $totalData = 0;
                        $maxData = 24 * $hourlyMax;
                    @endphp
                    <tr>
                        <td class="ct-date">{{ $dispDate }}</td>
                        @foreach ($hours24 as $h)
                            @php $val = $hourData[$h] ?? null; @endphp
                            @if ($val === null || $val === 0)
                                <td class="ct-none">✕</td>
                            @elseif ($hourlyMax > 0 && $val >= $hourlyMax)
                                @php $totalData += $val; @endphp
                                <td class="ct-full">{{ $val }}</td>
                            @else
                                @php $totalData += $val; @endphp
                                <td class="ct-part">{{ $val }}</td>
                            @endif
                        @endforeach
                        @php
                            $pctVal = $maxData > 0 ? round(($totalData / $maxData) * 100, 1) : 0;
                            $pctClass = $pctVal >= 84 ? 'ct-pct-g' : ($pctVal >= 50 ? 'ct-pct-y' : 'ct-pct-r');
                        @endphp
                        <td class="ct-total">{{ $totalData }}</td>
                        <td class="{{ $pctClass }}">{{ number_format($pctVal, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach



    {{-- PAGES 4+: Graphic Overview (6 charts per page, 2 columns × 3 rows) --}}

    @php
        $chartIndex = 0;
        // Label X-axis: 24 jam (00:00 - 23:00)
        $chartLabels = array_map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23));
    @endphp
    @foreach ($stats as $idx => $s)
        @php
            $paramName = $s['parameter_name'];
            $unit = $s['parameter_unit'] ?? '';
            $chartData = $hourly_charts[$paramName] ?? [];
            $color = $chartColors[$idx % count($chartColors)];
            $chartTitle = $s['parameter_label'] . ($unit ? '  (' . $unit . ')' : '');
            $isNewPage = $chartIndex % 6 === 0; // new page every 6 charts
            $isLeft = $chartIndex % 2 === 0; // left column every 2 charts
            $chartIndex++;

            $fmtV = fn($v) => $v !== null ? number_format($v, 2) : '-';
            $note =
                'Max: ' .
                $fmtV($s['max']) .
                '  (' .
                ($s['max_time'] ?? '-') .
                ' ' .
                ($s['max_date'] ?? '-') .
                ')' .
                '      Min: ' .
                $fmtV($s['min']) .
                '  (' .
                ($s['min_time'] ?? '-') .
                ' ' .
                ($s['min_date'] ?? '-') .
                ')' .
                '      Avg: ' .
                $fmtV($s['average']) .
                ($unit ? ' ' . $unit : '');
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
