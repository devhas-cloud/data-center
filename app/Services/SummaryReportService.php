<?php

namespace App\Services;

use App\Models\DataModel;
use App\Models\DeviceModel;
use App\Models\SensorModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SummaryReportService
{
    /**
     * Build the summary data array (same structure as getReportSummary controller).
     *
     * @param  string  $deviceId
     * @param  string  $startDate  Any parseable datetime string, app timezone
     * @param  string  $endDate    Any parseable datetime string, app timezone
     * @return array
     * @throws \Exception
     */


    public function getSummaryData(string $deviceId, string $startDate, string $endDate, string $type): array
    {
        $tz = config('app.timezone', 'UTC');

        $device = DeviceModel::where('device_id', $deviceId)
            ->select('device_id', 'device_category', 'device_hourly_data', 'latitude', 'longitude', 'location', 'district')
            ->first();

        if (!$device) {
            throw new \Exception("Device not found: {$deviceId}");
        }

        $sensors = SensorModel::where('tbl_sensor.device_id', $deviceId)
            ->where('tbl_sensor.status', 'active')
            ->join('tbl_parameter', 'tbl_sensor.parameter_name', '=', 'tbl_parameter.parameter_name')
            ->select('tbl_sensor.parameter_name', 'tbl_parameter.parameter_label', 'tbl_sensor.sensor_unit')
            ->orderBy('tbl_sensor.id', 'asc')
            ->get();

        if ($sensors->isEmpty()) {
            throw new \Exception("No active sensors for device: {$deviceId}");
        }

        $parameters    = $sensors->pluck('parameter_name')->toArray();
        $startDateUnix = Carbon::parse($startDate, $tz)->timestamp;
        $endDateUnix   = Carbon::parse($endDate, $tz)->timestamp;

        // ── Aggregates (avg / max / min) ──────────────────────────────────────
        $aggregates = DataModel::select(
            'parameter_name',
            DB::raw('ROUND(AVG(value), 2) as avg_value'),
            DB::raw('ROUND(MAX(value), 2) as max_value'),
            DB::raw('ROUND(MIN(value), 2) as min_value')
        )
            ->where('device_id', $deviceId)
            ->whereIn('parameter_name', $parameters)
            ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
            ->groupBy('parameter_name')
            ->get()
            ->keyBy('parameter_name');

        // ── Stats with max/min timestamps ─────────────────────────────────────
        $stats = [];
        foreach ($sensors as $sensor) {
            $paramName = $sensor->parameter_name;
            $agg       = $aggregates[$paramName] ?? null;
            $maxDt     = $minDt = null;

            if ($agg) {
                $maxTs = DataModel::where('device_id', $deviceId)
                    ->where('parameter_name', $paramName)
                    ->where('value', $agg->max_value)
                    ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                    ->orderBy('timestamp')
                    ->value('timestamp');

                $minTs = DataModel::where('device_id', $deviceId)
                    ->where('parameter_name', $paramName)
                    ->where('value', $agg->min_value)
                    ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                    ->orderBy('timestamp')
                    ->value('timestamp');

                $maxDt = $maxTs ? Carbon::createFromTimestamp($maxTs, 'UTC')->setTimezone($tz) : null;
                $minDt = $minTs ? Carbon::createFromTimestamp($minTs, 'UTC')->setTimezone($tz) : null;
            }

            $stats[] = [
                'parameter_name'  => $paramName,
                'parameter_label' => $sensor->parameter_label ?? $paramName,
                'parameter_unit'  => $sensor->sensor_unit ?? '',
                'average'         => $agg ? $agg->avg_value : null,
                'max'             => $agg ? $agg->max_value : null,
                'max_time'        => $maxDt ? $maxDt->format('H:i') : null,
                'max_date'        => $maxDt ? $maxDt->format('d/m/Y') : null,
                'min'             => $agg ? $agg->min_value : null,
                'min_time'        => $minDt ? $minDt->format('H:i') : null,
                'min_date'        => $minDt ? $minDt->format('d/m/Y') : null,
            ];
        }

        // ── Hourly averages per parameter (24-slot profile for charts) ─────────
        $hourlyRows = DataModel::select(
            DB::raw('HOUR(FROM_UNIXTIME(`timestamp`)) as hour'),
            'parameter_name',
            DB::raw('ROUND(AVG(value), 2) as avg_value')
        )
            ->where('device_id', $deviceId)
            ->whereIn('parameter_name', $parameters)
            ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
            ->groupBy('hour', 'parameter_name')
            ->orderBy('hour')
            ->get();

        $hourlyCharts = [];
        foreach ($parameters as $p) {
            $hourlyCharts[$p] = array_fill(0, 24, null);
        }
        foreach ($hourlyRows as $row) {
            if (isset($hourlyCharts[$row->parameter_name])) {
                $hourlyCharts[$row->parameter_name][(int) $row->hour] = $row->avg_value !== null ? floatval($row->avg_value) : null;
            }
        }

        // ── Flexible datetime-grouped hourly data for table display ───────────
        $hourlyTableRows = DataModel::select(
            DB::raw('(FLOOR(`timestamp` / 3600) * 3600) as hour_ts'),
            'parameter_name',
            DB::raw('ROUND(AVG(value), 2) as avg_value')
        )
            ->where('device_id', $deviceId)
            ->whereIn('parameter_name', $parameters)
            ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
            ->groupBy('hour_ts', 'parameter_name')
            ->orderBy('hour_ts')
            ->get();

        $hourlyTableMap = [];
        foreach ($hourlyTableRows as $row) {
            $hts = (int) $row->hour_ts;
            if (!isset($hourlyTableMap[$hts])) {
                $hourlyTableMap[$hts] = [
                    'datetime' => Carbon::createFromTimestamp($hts, 'UTC')
                        ->setTimezone($tz)
                        ->format('d/m/Y H:i'),
                    'values'   => [],
                ];
            }
            $hourlyTableMap[$hts]['values'][$row->parameter_name] = $row->avg_value !== null ? floatval($row->avg_value) : null;
        }

        // ── Hourly Count Summary (distinct minute-slots per hour per date) ─────
        $startDateObj  = Carbon::parse($startDate, $tz);
        $endDateObj    = Carbon::parse($endDate, $tz);
        $deviceHourly  = (int) ($device->device_hourly_data ?? 0);

        $hourlyCount = [];
        foreach ($parameters as $paramName) {
            $sensor = $sensors->firstWhere('parameter_name', $paramName);

            $countRows = DB::table('tbl_data')
                ->selectRaw("DATE(FROM_UNIXTIME(`timestamp`)) as `date`")
                ->selectRaw("HOUR(FROM_UNIXTIME(`timestamp`)) as `hour`")
                ->selectRaw("COUNT(DISTINCT DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i')) as val")
                ->where('device_id', $deviceId)
                ->where('parameter_name', $paramName)
                ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                ->groupBy('date', 'hour')
                ->orderBy('date', 'asc')
                ->orderBy('hour', 'asc')
                ->get();

            // Pre-fill every date in range with null
            $byDate   = [];
            $allDates = [];
            $cursor   = $startDateObj->copy()->startOfDay();
            $dateEnd  = $endDateObj->copy()->startOfDay();
            while ($cursor->lte($dateEnd)) {
                $d          = $cursor->format('Y-m-d');
                $allDates[] = $d;
                $byDate[$d] = array_fill(0, 24, null);
                $cursor->addDay();
            }

            foreach ($countRows as $row) {
                $d = $row->date;
                $h = (int) $row->hour;
                if (!isset($byDate[$d])) {
                    $byDate[$d] = array_fill(0, 24, null);
                    $allDates[] = $d;
                }
                $byDate[$d][$h] = (int) $row->val;
            }
            sort($allDates);

            $hourlyCount[$paramName] = [
                'param_label'  => $sensor->parameter_label ?? $paramName,
                'param_unit'   => $sensor->sensor_unit ?? '',
                'hourly_data'  => $deviceHourly,
                'dates'        => $allDates,
                'by_date'      => $byDate,
            ];
        }

        // ── Period data (weekly / monthly): daily aggregates ──────────────────
        $dailyTable       = [];
        $dailyCharts      = [];
        $dailyChartLabels = [];
        $dailyCount       = [];

        if (in_array($type, ['weekly', 'monthly'])) {
            // Full list of dates in range
            $periodDates = [];
            $cur         = $startDateObj->copy()->startOfDay();
            $curEnd      = $endDateObj->copy()->startOfDay();
            while ($cur->lte($curEnd)) {
                $periodDates[] = $cur->format('Y-m-d');
                $cur->addDay();
            }
            $dateIndex = array_flip($periodDates);

            // Daily averages per parameter
            $dailyAvgRows = DataModel::select(
                DB::raw('DATE(FROM_UNIXTIME(`timestamp`)) as day_date'),
                'parameter_name',
                DB::raw('ROUND(AVG(value), 2) as avg_value')
            )
                ->where('device_id', $deviceId)
                ->whereIn('parameter_name', $parameters)
                ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                ->groupBy('day_date', 'parameter_name')
                ->orderBy('day_date')
                ->get();

            // Pre-fill chart arrays and table map
            foreach ($parameters as $p) {
                $dailyCharts[$p] = array_fill(0, count($periodDates), null);
            }
            $dailyTableMap = [];
            foreach ($periodDates as $d) {
                $dailyTableMap[$d] = [
                    'date'   => Carbon::createFromFormat('Y-m-d', $d, $tz)->format('d/m/Y'),
                    'values' => [],
                ];
            }

            foreach ($dailyAvgRows as $row) {
                $d = $row->day_date;
                if (isset($dateIndex[$d]) && isset($dailyCharts[$row->parameter_name])) {
                    $dailyCharts[$row->parameter_name][$dateIndex[$d]] =
                        $row->avg_value !== null ? floatval($row->avg_value) : null;
                }
                if (isset($dailyTableMap[$d])) {
                    $dailyTableMap[$d]['values'][$row->parameter_name] =
                        $row->avg_value !== null ? floatval($row->avg_value) : null;
                }
            }

            $dailyTable       = array_values($dailyTableMap);
            $dailyChartLabels = array_map(
                fn($d) => Carbon::createFromFormat('Y-m-d', $d, $tz)->format('d/m'),
                $periodDates
            );

            // Daily count: total records per day per parameter
            $dailyCountMap = [];
            foreach ($periodDates as $d) {
                $dailyCountMap[$d] = [];
            }

            foreach ($parameters as $paramName) {
                $cntRows = DB::table('tbl_data')
                    ->selectRaw("DATE(FROM_UNIXTIME(`timestamp`)) as `date`")
                    ->selectRaw("COUNT(*) as val")
                    ->where('device_id', $deviceId)
                    ->where('parameter_name', $paramName)
                    ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                    ->groupBy('date')
                    ->get();

                foreach ($cntRows as $row) {
                    if (!isset($dailyCountMap[$row->date])) {
                        $dailyCountMap[$row->date] = [];
                    }
                    $dailyCountMap[$row->date][$paramName] = (int) $row->val;
                }
            }

            $dailyCount = [
                'dates'     => $periodDates,
                'by_date'   => $dailyCountMap,
                'daily_max' => $deviceHourly * 24,  // expected records per parameter per day
            ];
        }

        return [
            'device_id'       => $deviceId,
            'device_category' => $device->device_category ?? $deviceId,
            'date_range'      => [
                'start' => Carbon::parse($startDate, $tz)->format('d/m/Y H:i'),
                'end'   => Carbon::parse($endDate, $tz)->format('d/m/Y H:i'),
            ],
            'stats'              => $stats,
            'parameters'         => $parameters,
            'hourly_charts'      => $hourlyCharts,
            'hourly_table'       => array_values($hourlyTableMap),
            'hourly_count'       => $hourlyCount,
            'daily_table'        => $dailyTable,
            'daily_charts'       => $dailyCharts,
            'daily_chart_labels' => $dailyChartLabels,
            'daily_count'        => $dailyCount,
            'latitude'           => $device->latitude  ? (float) $device->latitude  : null,
            'longitude'          => $device->longitude ? (float) $device->longitude : null,
            'location'           => $device->location  ?? null,
            'district'           => $device->district  ?? null,
        ];
    }



    /**
     * Generate the summary report PDF and return its raw binary string.
     */
    public function generatePdfOutput(string $deviceId, string $startDate, string $endDate, string $type): string
    {
        $data = $this->getSummaryData($deviceId, $startDate, $endDate, $type);

        // Embed map tile image so DomPDF can render it offline
        $data['map_image'] = null;
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $data['map_image'] = $this->fetchMapImage(
                (float) $data['latitude'],
                (float) $data['longitude']
            );
        }

        $pdf = Pdf::loadView("pdf.summary-report-{$type}", $data)
            ->setPaper('A4', 'landscape')
            ->setOption('dpi', 150)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif');

        // Render first, then add per-page footer via canvas (avoids position:fixed DomPDF bug)
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas    = $dompdf->getCanvas();
        $pageH     = $canvas->get_height();
        $pageW     = $canvas->get_width();
        $leftText  = "";
        $rightText = 'Powered by PT. HAS ENVIRONMENTAL';

        $canvas->page_script(function (int $pageNumber, int $pageCount, $pdf, $fontMetrics)
        use ($pageH, $pageW, $leftText, $rightText) {
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            // Separator line
            $pdf->line(14, $pageH - 20, $pageW - 14, $pageH - 20, [0.8, 0.8, 0.8], 0.4);
            // Left label
            $pdf->text(14, $pageH - 15, $leftText, $font, 7, [0.43, 0.46, 0.48]);
            // Right label (page number)
            $right = "{$rightText}  |  {$pageNumber}/{$pageCount}";
            $tw = $pdf->get_text_width($right, $font, 7);
            $pdf->text($pageW - $tw - 14, $pageH - 15, $right, $font, 7, [0.20, 0.24, 0.29]);
        });

        return $dompdf->output();
    }

    /**
     * Fetch a 3×2 grid of OSM tiles centred on lat/lon and return a base64 PNG data URI.
     * Requires the GD extension. Returns null on any failure so the map is simply skipped.
     */
    private function fetchMapImage(float $lat, float $lon, int $zoom = 13): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $n    = 2 ** $zoom;
        $xFull = ($lon + 180) / 360 * $n;
        $latRad = deg2rad($lat);
        $yFull = (1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $n;

        $xC = (int) floor($xFull);
        $yC = (int) floor($yFull);

        // 3 columns × 2 rows centred on the device tile
        $cols = [$xC - 1, $xC, $xC + 1];
        $rows = [$yC - 1, $yC];

        $canvasW = 3 * 256;
        $canvasH = 2 * 256;

        $canvas = imagecreatetruecolor($canvasW, $canvasH);
        $bgGrey = imagecolorallocate($canvas, 200, 200, 200);
        imagefill($canvas, 0, 0, $bgGrey);

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header'  => "User-Agent: HAS-ENVIRONMENTAL-PDF-Generator/1.0 (report@has-environmental.com)\r\n",
            ],
        ]);

        foreach ($rows as $rowIdx => $yTile) {
            foreach ($cols as $colIdx => $xTile) {
                $url      = "https://tile.openstreetmap.org/{$zoom}/{$xTile}/{$yTile}.png";
                $tileData = @file_get_contents($url, false, $ctx);
                if ($tileData) {
                    $tile = @imagecreatefromstring($tileData);
                    if ($tile) {
                        imagecopy($canvas, $tile, $colIdx * 256, $rowIdx * 256, 0, 0, 256, 256);
                        imagedestroy($tile);
                    }
                }
            }
        }

        // Exact pixel position of lat/lon on the composed canvas
        $px = (int) round(($xFull - ($xC - 1)) * 256);
        $py = (int) round(($yFull - ($yC - 1)) * 256);

        // Draw marker: red filled circle with white + dark border
        $colRed   = imagecolorallocate($canvas, 220, 53, 69);
        $colWhite = imagecolorallocate($canvas, 255, 255, 255);
        $colDark  = imagecolorallocate($canvas, 52, 58, 64);
        imagefilledellipse($canvas, $px, $py, 20, 20, $colRed);
        imageellipse($canvas, $px, $py, 20, 20, $colWhite);
        imageellipse($canvas, $px, $py, 22, 22, $colDark);

        // Scale canvas to 1264×500 (A4 width minus margins) for better PDF embedding
        $finalW = 1264;
        $finalH = 500;

        $final = imagecreatetruecolor($finalW, $finalH);

        imagecopyresampled(
            $final,
            $canvas,
            0,
            0,
            0,
            0,
            $finalW,
            $finalH,
            $canvasW,
            $canvasH
        );
        imagedestroy($canvas);

        ob_start();
        imagepng($final);
        $pngData = ob_get_clean();
        imagedestroy($final);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}
