<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryModel;
use App\Models\DeviceModel;
use App\Models\DataModel;
use App\Models\LatestDataModel;
use App\Models\SensorModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $cacheKey = "admin:dashboard:devices";
        $cacheTTL = 2; // Cache 2 menit

        // Check if cached
        if (Cache::has($cacheKey)) {
            return view('admin.manage_dashboard', [
                'deviceCategories' => Cache::get($cacheKey)
            ]);
        }

        if(Auth::user()->level == 'master'){
            // Jika master, tampilkan semua device categories dan devices dengan optimized queries
            $deviceCategories = CategoryModel::with(['devices' => function ($query) {
                $query->select('device_id', 'device_name', 'location', 'latitude', 'longitude', 'device_category',  'device_gap_timeout')
                      ->orderBy('device_id', 'asc');
            }])
            ->select( 'category_name', 'category_icon')
            ->orderBy('category_name', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'device_category' => $category->category_name,
                    'category_icon' => $category->category_icon,
                    'devices' => $category->devices->map(function ($device) {
                        return [
                            'device_id' => $device->device_id,
                            'device_name' => $device->device_name,
                            'location' => $device->location,
                            'latitude' => $device->latitude,
                            'longitude' => $device->longitude,
                            'status' => $this->DeviceStatus($device->device_id, $device->device_gap_timeout),
                        ];
                    })->toArray(),
                ];
            })->toArray();

        } else {
            // Selain master, tampilkan device categories dan devices yang usernya user assigned ke user yang login
            $deviceCategories = CategoryModel::with(['devices' => function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id)
                      ->select('device_id', 'device_name', 'location', 'latitude', 'longitude', 'device_category',  'device_gap_timeout', 'user_assigned')
                      ->orderBy('device_id', 'asc');
            }])
            ->select( 'category_name', 'category_icon')
            ->whereHas('devices', function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id);
            })
            ->orderBy('category_name', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'device_category' => $category->category_name,
                    'category_icon' => $category->category_icon,
                    'devices' => $category->devices->map(function ($device) {
                        return [
                            'device_id' => $device->device_id,
                            'device_name' => $device->device_name,
                            'location' => $device->location,
                            'latitude' => $device->latitude,
                            'longitude' => $device->longitude,
                            'status' => $this->DeviceStatus($device->device_id, $device->device_gap_timeout),
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }

        // Cache hasil selama 2 menit
        Cache::put($cacheKey, $deviceCategories, now()->addMinutes($cacheTTL));

        return view('admin.manage_dashboard', [
            'deviceCategories' => $deviceCategories
        ]);
    }


    public function DeviceStatus($deviceId, $gapTimeout = 3)
    {
        // lakukan ping ke IP address tersebut
        // $ipAddress = $deviceIp;
        // $pingResult = exec("ping -c 1 " . escapeshellarg($ipAddress), $output, $status);
        // if ($status === 0) {
        //     $statusMessage = "Online";
        // } else {
        //     $statusMessage = "Offline";
        // }
        
        # Perbaiki metode pengecekan status dimana jika data terakhir lebih dari 6 menit yang lalu, maka dianggap offline
        $latestData = DataModel::where('device_id', $deviceId)->orderBy('timestamp', 'desc')->first();
        if (!$latestData) {
            return "Offline";
        }
        $latestTimestamp = Carbon::createFromTimestamp($latestData->timestamp);
        $now = Carbon::now();
        $diffInMinutes = $latestTimestamp->diffInMinutes($now);
        if ($diffInMinutes > $gapTimeout) {
            return "Offline";
        } else {
            return "Online";
        }
    }

    public function getMapsDashboard($deviceId)
    {
        $cacheKey = "device:{$deviceId}:maps";
        $cacheTTL = 2; // Cache 2 menit

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $device = DeviceModel::where('device_id', $deviceId)
            ->with('category')
            ->select('device_id', 'device_name', 'device_category',  'latitude', 'longitude', 'device_gap_timeout')
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $mapData = [
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'device_icon' => $device->category ? $device->category->category_icon : null,
            'device_category' => $device->device_category,
            'latitude' => $device->latitude,
            'longitude' => $device->longitude,
            'status' => $this->DeviceStatus($device->device_id, $device->device_gap_timeout),
        ];

        Cache::put($cacheKey, $mapData, now()->addMinutes($cacheTTL));

        return response()->json($mapData, 200);
    }


    public function progressBar($deviceId)
    {
        $cacheKey = "device:{$deviceId}:progress";
        $cacheTTL = 1;

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // STEP 1: Get all sensors + parameters (1 query)
        $sensors = SensorModel::where('device_id', $deviceId)
            ->join('tbl_parameter', 'tbl_sensor.parameter_name', '=', 'tbl_parameter.parameter_name')
            ->select(
                'tbl_sensor.id',
                'tbl_sensor.device_id',
                'tbl_sensor.parameter_name',
                'tbl_sensor.sensor_unit',
                'tbl_sensor.parameter_indicator_min',
                'tbl_sensor.parameter_indicator_max',
                'tbl_sensor.parameter_indicator_alert',
                'tbl_parameter.parameter_label'
            )
            ->get()
            ->keyBy('parameter_name'); // Key by parameter_name untuk matching

        if ($sensors->isEmpty()) {
            Cache::put($cacheKey, [], now()->addMinutes($cacheTTL));
            return response()->json([]);
        }

        // STEP 2: Get LATEST data untuk SEMUA sensor sekaligus (1 query, bukan N query!)
        // Gunakan MAX(id) per parameter_name untuk efficiency
        $latestDataIds = LatestDataModel::select(DB::raw('MAX(id) as id'))
            ->where('device_id', $deviceId)
            ->whereIn('parameter_name', $sensors->keys()->toArray())
            ->groupBy('parameter_name')
            ->pluck('id');

        // STEP 3: Fetch actual data rows (1 query)
        $latestDataCollection = LatestDataModel::whereIn('id', $latestDataIds)
            ->get()
            ->keyBy('parameter_name'); // Key by parameter_name untuk easy lookup

        // STEP 4: Build response dengan data yang sudah di-load di memory
        $data = $sensors->map(function ($sensor) use ($latestDataCollection) {
            $latestData = $latestDataCollection->get($sensor->parameter_name);

            return [
                'parameter_label' => $sensor->parameter_label,
                'parameter_name' => $sensor->parameter_name,
                'latest_value' => $latestData ? $latestData->value : null,
                'recorded_at' => $latestData ? $this->unixToDateTime($latestData->timestamp)->format('Y-m-d H:i:s') : null,
                'parameter_indicator_min' => $sensor->parameter_indicator_min,
                'parameter_indicator_max' => $sensor->parameter_indicator_max,
                'sensor_unit' => $sensor->sensor_unit,
            ];
        })->values(); // values() untuk reset array keys

        Cache::put($cacheKey, $data, now()->addMinutes($cacheTTL));

        return response()->json($data);
    }

    public function lineChartData(Request $request, $deviceId)
    {
        $parameter = $request->query('parameter');

        // Validate parameter
        if (!$parameter) {
            return response()->json(['error' => 'Parameter is required'], 400);
        }

        $cacheKey = "device:{$deviceId}:chart:line:{$parameter}";
        $cacheTTL = 1;

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Get sensor unit (cache sensor info)
        $sensorCacheKey = "device:{$deviceId}:sensor:{$parameter}";
        if (Cache::has($sensorCacheKey)) {
            $sensor = Cache::get($sensorCacheKey);
        } else {
            $sensor = SensorModel::where('device_id', $deviceId)
                ->where('parameter_name', $parameter)
                ->with('parameter')
                ->first();
            Cache::put($sensorCacheKey, $sensor, now()->addMinutes(10));
        }

        $unit = $sensor ? $sensor->sensor_unit : '';

        // tampilkan data 24 jam terakhir
        $now = now();
        $startTime = $now->copy()->subHours(24);
        $endTime = $now->copy();

        $data = DataModel::where('device_id', $deviceId)
            ->where('parameter_name', $parameter)
            ->where('timestamp', '>=', $startTime->timestamp)
            ->where('timestamp', '<=', $endTime->timestamp)
            ->orderBy('timestamp', 'asc')
            ->get();

        $labels = [];
        $values = [];
        $previousTime = null;

        $gapTimeout = 1;
        if ($device = DeviceModel::where('device_id', $deviceId)->first(['device_id', 'device_gap_timeout'])) {
            $gapTimeout = $device->device_gap_timeout ?? 1;
        }

        foreach ($data as $item) {
            $currentTime = Carbon::createFromTimestamp($item->timestamp, 'UTC')
                ->setTimezone(config('app.timezone'));

            if ($previousTime !== null) {
                $diffInMinutes = $previousTime->diffInMinutes($currentTime);

                if ($diffInMinutes > $gapTimeout) {
                    $labels[] = $previousTime->copy()->addMinute()->format('Y-m-d H:i');
                    $values[] = null;

                    $labels[] = $currentTime->copy()->subMinute()->format('Y-m-d H:i');
                    $values[] = null;
                }
            }

            $labels[] = $currentTime->format('Y-m-d H:i');
            $values[] = round(floatval($item->value), 2);
            $previousTime = $currentTime;
        }

        $chartData = [
            'labels' => $labels,
            'values' => $values,
            'parameter_label' => $sensor ? $sensor->parameter->parameter_label : $sensor->parameter_name,
            'unit' => $unit,
        ];

        Cache::put($cacheKey, $chartData, now()->addMinutes($cacheTTL));

        return response()->json($chartData);
    }


    public function barChartData(Request $request, $deviceId)
    {
        $parameter = $request->query('parameter');

        // Validate parameter
        if (!$parameter) {
            return response()->json(['error' => 'Parameter is required'], 400);
        }

        $cacheKey = "device:{$deviceId}:chart:bar:{$parameter}";
        $cacheTTL = 5;

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Get sensor unit (cache sensor info)
        $sensorCacheKey = "device:{$deviceId}:sensor:{$parameter}";
        if (Cache::has($sensorCacheKey)) {
            $sensor = Cache::get($sensorCacheKey);
        } else {
            $sensor = SensorModel::where('device_id', $deviceId)
                ->where('parameter_name', $parameter)
                ->with('parameter')
                ->first();
            Cache::put($sensorCacheKey, $sensor, now()->addMinutes(10));
        }

        $unit = $sensor ? $sensor->sensor_unit : '';

        // Rolling 24 hours window - from 24 hours ago to now
        $now = now();
        $startTime = $now->copy()->subHours(24);
        $endTime = $now->copy();

        // Aggregate data hourly
        $data = DataModel::where('device_id', $deviceId)
            ->where('parameter_name', $parameter)
            ->where('timestamp', '>=', $startTime->timestamp)
            ->where('timestamp', '<=', $endTime->timestamp)
            ->select(DB::raw('DATE_FORMAT(FROM_UNIXTIME(timestamp), "%Y-%m-%d %H:00") as hour'), DB::raw('AVG(value) as avg_value'))
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        $labels = [];
        $values = [];
        foreach ($data as $item) {
            $labels[] = $item->hour;
            $values[] = round(floatval($item->avg_value), 2);
        }

        $chartData = [
            'labels' => $labels,
            'values' => $values,
            'parameter_label' => $sensor ? $sensor->parameter->parameter_label : $sensor->parameter_name,
            'unit' => $unit
        ];

        Cache::put($cacheKey, $chartData, now()->addMinutes($cacheTTL));

        return response()->json($chartData);
    }


    public function windroseData($deviceId)
    {
        try {
            $cacheKey = "device:{$deviceId}:windrose";
            $cacheTTL = 5;

            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            $now = Carbon::now();
            $startTime = $now->copy()->subHours(24);

            // Query dengan select spesifik untuk efficiency
            $result = DB::table('tbl_data')
                ->selectRaw('FROM_UNIXTIME(timestamp, "%Y-%m-%d %H:%i") as date,
                             AVG(CASE WHEN parameter_name = "wspeed" THEN value END) as wspeed,
                             AVG(CASE WHEN parameter_name = "wdir" THEN value END) as wdir')
                ->whereIn('parameter_name', ['wspeed', 'wdir'])
                ->where('timestamp', '>=', $startTime->timestamp)
                ->where('device_id', $deviceId)
                ->groupByRaw('FROM_UNIXTIME(timestamp, "%Y-%m-%d %H:%i")')
                ->orderBy('date', 'asc')
                ->get();

            $timestamps = [];
            $wspeed = [];
            $wdir = [];

            foreach ($result as $row) {
                $timestamps[] = (string) $row->date;
                $wspeed[] = $row->wspeed !== null ? floatval($row->wspeed) : null;
                $wdir[] = $row->wdir !== null ? floatval($row->wdir) : null;
            }

            $windroseData = [
                "timestamps" => $timestamps,
                "wspeed" => $wspeed,
                "wdir" => $wdir
            ];

            Cache::put($cacheKey, $windroseData, now()->addMinutes($cacheTTL));

            return response()->json($windroseData);
        } catch (\Exception $e) {
            return response()->json([
                "timestamps" => [],
                "wspeed" => [],
                "wdir" => [],
                "error" => $e->getMessage()
            ], 500);
        }
    }


    public function getHistoricalChartData(Request $request, $deviceId)
    {
        try {
            $parameterName = $request->input('parameter');
            $startDateStr = $request->input('start_date');
            $endDateStr = $request->input('end_date');

            if (!$parameterName || !$startDateStr || !$endDateStr) {
                return response()->json([
                    'labels' => [],
                    'values' => [],
                    'unit' => '',
                    'error' => 'Missing required parameters'
                ], 400);
            }

            // Create cache key dengan date range
            $cacheKey = "device:{$deviceId}:chart:historical:{$parameterName}:{$startDateStr}:{$endDateStr}";
            $cacheTTL = 5;

            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            // ubah format tanggal ke unix timestamp
            $startDate = Carbon::parse($startDateStr)->timestamp;
            $endDate = Carbon::parse($endDateStr)->timestamp;

            // Query data tanpa aggregation - ambil semua data points
            $data = DataModel::where('device_id', $deviceId)
                ->where('parameter_name', $parameterName)
                ->whereBetween('timestamp', [$startDate, $endDate])
                ->orderBy('timestamp', 'asc')
                ->get();

            // Get sensor unit (cache sensor info)
            $sensorCacheKey = "device:{$deviceId}:sensor:{$parameterName}";
            if (Cache::has($sensorCacheKey)) {
                $sensor = Cache::get($sensorCacheKey);
            } else {
                $sensor = SensorModel::where('device_id', $deviceId)
                    ->where('parameter_name', $parameterName)
                    ->with('parameter')
                    ->first();
                Cache::put($sensorCacheKey, $sensor, now()->addMinutes(10));
            }

            $unit = $sensor ? $sensor->sensor_unit : '';

            // Prepare labels and values with gap detection
            $labels = [];
            $values = [];
            $previousTime = null;

            $gapTimeout = 1;
            if ($device = DeviceModel::where('device_id', $deviceId)->first(['device_id', 'device_gap_timeout'])) {
                $gapTimeout = $device->device_gap_timeout ?? 1;
            }

            foreach ($data as $item) {
                $currentTime = Carbon::createFromTimestamp($item->timestamp, 'UTC')
                    ->setTimezone(config('app.timezone'));

                if ($previousTime !== null) {
                    $diffInMinutes = $previousTime->diffInMinutes($currentTime);

                    if ($diffInMinutes > $gapTimeout) {
                        $labels[] = $previousTime->copy()->addMinute()->format('Y-m-d H:i');
                        $values[] = null;

                        $labels[] = $currentTime->copy()->subMinute()->format('Y-m-d H:i');
                        $values[] = null;
                    }
                }

                $labels[] = $currentTime->format('Y-m-d H:i');
                $values[] = round(floatval($item->value), 2);
                $previousTime = $currentTime;
            }

            $chartData = [
                'labels' => $labels,
                'values' => $values,
                'parameter_label' => $sensor ? $sensor->parameter->parameter_label : $sensor->parameter_name,
                'unit' => $unit
            ];

            Cache::put($cacheKey, $chartData, now()->addMinutes($cacheTTL));

            return response()->json($chartData);
        } catch (\Exception $e) {
            return response()->json([
                'labels' => [],
                'values' => [],
                'parameter_label' => '',
                'unit' => '',
                'error' => 'Failed to retrieve data: ' . $e->getMessage()
            ], 500);
        }
    }

}
