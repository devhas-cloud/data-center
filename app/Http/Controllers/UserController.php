<?php

namespace App\Http\Controllers;

use App\Models\AccessModel;
use App\Models\AutoReportModel;
use App\Models\CategoryModel;
use App\Models\DeviceModel;
use App\Models\DataModel;
use App\Models\GuidanceModel;
use App\Models\LatestDataModel;
use App\Models\LogsModel;
use App\Models\ParameterModel;
use App\Models\SensorModel;
use App\Models\SyslogHeaderModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

//use Dompdf\Dompdf;
//use Dompdf\Options;

class UserController extends Controller
{
    // ambil device Id milik user yang l

    public function home()
    {

        return view('user.home');
    }


    public function getDeviceForHome()
    {
        $userId = Auth::user()->id;
        $cacheKey = "user:{$userId}:devices:home";
        $cacheTTL = 2; // Cache 2 menit

        // Check if cached
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // STEP 1: Eager load dengan select spesifik (Hemat Memory & Query)
        // Hanya ambil kolom yang dibutuhkan, jangan select(*)
        $userAccess = AccessModel::where('user_id', $userId)
            ->with([
                'category:id,category_name,category_icon',
                'device:id,device_id,device_name,latitude,longitude,device_gap_timeout',
                'device.sensors:id,device_id,parameter_name'
            ])
            ->get(['id', 'user_id', 'category_id', 'device_id']); // Select parent columns

        // Early exit jika tidak ada data akses
        if ($userAccess->isEmpty()) {
            Cache::put($cacheKey, [], now()->addMinutes($cacheTTL));
            return response()->json([]);
        }

        // STEP 2: Collect device IDs dan parameter names menggunakan Pluck (Lebih Cepat)
        // pluck() jauh lebih efisien daripada loop foreach
        $deviceIds = $userAccess->pluck('device.device_id')->filter()->unique()->toArray();

        // Early exit jika struktur data tidak lengkap
        if (empty($deviceIds)) {
            Cache::put($cacheKey, [], now()->addMinutes($cacheTTL));
            return response()->json([]);
        }

        // STEP 3: Fetch actual data rows
        // Jika ID kosong, return collection kosong agar tidak error di whereIn

        $latestDataCollection = LatestDataModel::whereIn('device_id', $deviceIds)
                ->get()
                ->keyBy(function ($item) {
                    return $item->device_id . '|' . $item->parameter_name;
                });

        $result = $userAccess->groupBy('category.category_name')
            ->map(function ($group) use ($latestDataCollection) {
                $category = $group->first()->category;

                // Skip jika kategori null
                if (!$category) {
                    return null;
                }

                $devices = $group->filter(function ($access) {
                    return $access->device !== null;
                })->map(function ($access) use ($latestDataCollection) {
                    $device = $access->device;

                    $sensorsData = $device->sensors->map(function ($sensor) use ($device, $latestDataCollection) {
                        $key = $device->device_id . '|' . $sensor->parameter_name;
                        $latestData = $latestDataCollection->get($key);

                        // Jika tidak ada latest data, return null (akan difilter di bawah)
                        if (!$latestData) {
                            return null;
                        }

                        return [
                            'parameter_label' => $sensor->parameter ? $sensor->parameter->parameter_label : $sensor->parameter_name,
                            'parameter_name' => $sensor->parameter_name,
                            'latest_value'   => $latestData->value,
                            'recorded_at'    => $this->unixToDateTime($latestData->timestamp)->format('Y-m-d H:i:s'),
                        ];
                    })->filter()->values(); // Hapus null dan re-index

                    return [
                        'device_id'   => $device->device_id,
                        'device_name' => $device->device_name,
                        'latitude'    => $device->latitude,
                        'longitude'   => $device->longitude,
                        'status'      => $this->DeviceStatus($device->device_id, $device->device_gap_timeout),
                        'sensors'     => $sensorsData->toArray(),
                    ];
                })->values();

                // Return null jika tidak ada device valid di kategori ini
                return $devices->isEmpty() ? null : [
                    'device_category' => $category->category_name,
                    'category_icon'   => $category->category_icon,
                    'devices'         => $devices->toArray(),
                ];
            })->filter()->values(); // Hapus kategori null dan re-index hasil akhir

        // Simpan cache
        Cache::put($cacheKey, $result, now()->addMinutes($cacheTTL));

        return response()->json($result);
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


    public function dashboard()
    {

        $userId = Auth::user()->id;
        $cacheKey = "user:{$userId}:devices:dashboard";
        $cacheTTL = 5; // Cache 5 menit

        // Check if cached
        if (Cache::has($cacheKey)) {
            return view('user.dashboard', ['deviceCategories' => Cache::get($cacheKey)]);
        }

        // STEP 1: Eager load dengan optimized relationships
        $userAccess = AccessModel::where('user_id', $userId)
            ->with(['category', 'device'])
            ->get();

        // Early exit jika tidak ada data
        if ($userAccess->isEmpty()) {
            Cache::put($cacheKey, [], now()->addMinutes($cacheTTL));
            return view('user.dashboard', ['deviceCategories' => []]);
        }

        // STEP 2: Build response dengan collection methods (memory operations, no queries)
        $deviceCategories = $userAccess->groupBy('category.category_name')
            ->map(function ($group) {
                $category = $group->first()->category;

                if (!$category) {
                    return null;
                }

                $devices = $group->filter(function ($access) {
                    return $access->device !== null;
                })->map(function ($access) {
                    $device = $access->device;

                    return [
                        'device_id' => $device->device_id,
                        'device_name' => $device->device_name,
                        'location' => $device->location,
                        'latitude' => $device->latitude,
                        'longitude' => $device->longitude,
                        'status' => $this->DeviceStatus($device->device_id, $device->device_gap_timeout),
                    ];
                })->values();

                return !$devices->isEmpty() ? [
                    'device_category' => $category->category_name,
                    'category_icon' => $category->category_icon,
                    'devices' => $devices->toArray(),
                ] : null;
            })->filter()->values()->toArray();

        // Cache hasil selama 5 menit
        Cache::put($cacheKey, $deviceCategories, now()->addMinutes($cacheTTL));

        return view('user.dashboard', ['deviceCategories' => $deviceCategories]);
    }

    public function getMapsDashboard($deviceId)
    {
        $cacheKey = "device:{$deviceId}:maps";
        $cacheTTL = 1; // Cache 1 menit

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $device = DeviceModel::where('device_id', $deviceId)
            ->with('category')
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
        $cacheTTL = 1; // Cache 1 menit untuk chart

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
                ->first();
            Cache::put($sensorCacheKey, $sensor, now()->addMinutes(10));
        }

        $unit = $sensor ? $sensor->sensor_unit : '';

        // tampilkan data 24 jam terakhir
        $now = now();
        $startTime = $now->copy()->subHours(24); // 24 jam yang lalu
        $endTime = $now->copy(); // Waktu sekarang

        //ubah menjadi unix timestamp

        $data = DataModel::where('device_id', $deviceId)
            ->where('parameter_name', $parameter)
            ->where('timestamp', '>=', $startTime->timestamp)
            ->where('timestamp', '<=', $endTime->timestamp)
            ->orderBy('timestamp', 'asc')
            ->get();


        $labels = [];
        $values = [];
        $previousTime = null;

        $gapTimeout = 1; // Default gap timeout in minutes
        if ($device = DeviceModel::where('device_id', $deviceId)->first()) {
            $gapTimeout = $device->device_gap_timeout ?? 1;
        }


        foreach ($data as $item) {
            $currentTime = Carbon::createFromTimestamp($item->timestamp, 'UTC')
                ->setTimezone(config('app.timezone'));
            // Jika ada data sebelumnya, cek gap waktunya

            if ($previousTime !== null) {
                $diffInMinutes = $previousTime->diffInMinutes($currentTime);

                // Jika gap lebih dari 5 menit, tambahkan null value untuk membuat gap
                if ($diffInMinutes > $gapTimeout) {
                    // Tambahkan point null tepat setelah data terakhir
                    $labels[] = $previousTime->copy()->addMinute()->format('Y-m-d H:i');
                    $values[] = null;

                    // Tambahkan point null tepat sebelum data baru
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
        $cacheTTL = 5; // Cache 5 menit

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
                ->first();
            Cache::put($sensorCacheKey, $sensor, now()->addMinutes(10));
        }

        $unit = $sensor ? $sensor->sensor_unit : '';

        // Rolling 24 hours window - from 24 hours ago to now
        $now = now();
        $startTime = $now->copy()->subHours(24); // 24 hours ago
        $endTime = $now->copy(); // Current time

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
            $cacheTTL = 5; // Cache 5 menit

            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            $now = Carbon::now();
            // Konversi range waktu - default 24 jam terakhir
            $startTime =  $now->copy()->subHours(24); // Default: 24 jam terakhir


            // Query seperti Python UNION data + tmp
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

            // Ubah ke array untuk JSON
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
            $cacheTTL = 5; // Cache 5 menit untuk historical data

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
                    ->first();
                Cache::put($sensorCacheKey, $sensor, now()->addMinutes(10));
            }
            $unit = $sensor ? $sensor->sensor_unit : '';
            // Prepare labels and values with gap detection
            $labels = [];
            $values = [];
            $previousTime = null;

            $gapTimeout = 1; // Default gap timeout in minutes
            if ($device = DeviceModel::where('device_id', $deviceId)->first()) {
                $gapTimeout = $device->device_gap_timeout ?? 1;
            }

            foreach ($data as $item) {
                $currentTime = Carbon::createFromTimestamp($item->timestamp, 'UTC')
                    ->setTimezone(config('app.timezone'));

                // Jika ada data sebelumnya, cek gap waktunya
                if ($previousTime !== null) {
                    $diffInMinutes = $previousTime->diffInMinutes($currentTime);

                    // Jika gap lebih dari 5 menit, tambahkan null value untuk membuat gap
                    if ($diffInMinutes > $gapTimeout) {
                        // Tambahkan point null tepat setelah data terakhir
                        $labels[] = $previousTime->copy()->addMinute()->format('Y-m-d H:i');
                        $values[] = null;

                        // Tambahkan point null tepat sebelum data baru
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



    public function deviceInfo()
    {
        $userId = Auth::user()->id;
        $cacheKey = "user:{$userId}:device-info";
        $cacheTTL = 5; // Cache 5 menit

        // Check if cached
        if (Cache::has($cacheKey)) {
            return view('user.device_info', ['deviceCategories' => Cache::get($cacheKey)]);
        }

        // STEP 1: Eager load dengan optimized relationships
        $userAccess = AccessModel::where('user_id', $userId)
            ->with(['category', 'device'])
            ->get();

        // Early exit jika tidak ada data
        if ($userAccess->isEmpty()) {
            Cache::put($cacheKey, [], now()->addMinutes($cacheTTL));
            return view('user.device_info', ['deviceCategories' => []]);
        }

        // STEP 2: Build response dengan collection methods (memory operations, no queries)
        $result = $userAccess->groupBy('category.category_name')
            ->map(function ($group) {
                $category = $group->first()->category;

                if (!$category) {
                    return null;
                }

                $devices = $group->filter(function ($access) {
                    return $access->device !== null;
                })->map(function ($access) {
                    $device = $access->device;
                    return [
                        'device_id' => $device->device_id,
                        'device_name' => $device->device_name,
                    ];
                })->values();

                return !$devices->isEmpty() ? [
                    'device_category' => $category->category_name,
                    'devices' => $devices->toArray(),
                ] : null;
            })->filter()->values()->toArray();

        // Cache hasil selama 5 menit
        Cache::put($cacheKey, $result, now()->addMinutes($cacheTTL));

        return view('user.device_info', ['deviceCategories' => $result]);
    }

    public function getDeviceInfo($deviceId)
    {
        $cacheKey = "device:{$deviceId}:info";
        $cacheTTL = 5; // Cache 5 menit

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $device = DeviceModel::where('device_id', $deviceId)
            ->with('category')
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // Cache latest data per sensor
        $latestDataCacheKey = "device:{$deviceId}:latest";
        if (Cache::has($latestDataCacheKey)) {
            $latestData = Cache::get($latestDataCacheKey);
        } else {
            $latestData = DataModel::where('device_id', $deviceId)
                ->orderBy('timestamp', 'desc')
                ->first();
            Cache::put($latestDataCacheKey, $latestData, now()->addMinutes(2));
        }

        $dataDevice = [
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'device_status' => $this->DeviceStatus($device->device_id, $device->device_gap_timeout),
            'device_last_update_data' => $latestData ? $this->unixToDateTime($latestData->timestamp)->format('Y-m-d H:i') : 'No data',
            'device_category' => $device->device_category,
            'device_location' => $device->device_location,
            'device_date_installation' => $device->date_installation,
            'device_linked_img' => $device->linked_img,
            'device_district' => $device->district,
            'device_latitude' => $device->latitude,
            'device_longitude' => $device->longitude,
        ];

        $deviceConfig = SensorModel::where('device_id', $deviceId)->where('status', 'active')->get();
        $dataConfig = [];
        foreach ($deviceConfig as $config) {
            $parameter = ParameterModel::where('parameter_name', $config->parameter_name)->first();
            $dataConfig[] = [
                'parameter' => $parameter ? $parameter->parameter_label : null,
                'sensor_name' => $config->sensor_name,
                'device_name' => $config->sensor_number,
                'unit' => $config->sensor_unit,
                'calibration_date' => $config->calibration_date,
                'maintenance_date' => $config->maintenance_date,
            ];
        }

        $sysLogHeaders = SyslogHeaderModel::where('device_id', $deviceId)
            ->orderBy('created_date', 'desc')
            ->get()
            ->map(function ($syslog) {
                return [
                    'id' => $syslog->id,
                    'created_date' => $syslog->created_date,
                    'category' => $syslog->category,
                    'note' => $syslog->note,
                    'linked_file' => $syslog->linked_file,
                ];
            });

        $deviceInfo = [
            'device' => $dataDevice,
            'configuration' => $dataConfig,
            'syslogs' => $sysLogHeaders,
        ];

        Cache::put($cacheKey, $deviceInfo, now()->addMinutes($cacheTTL));

        return response()->json($deviceInfo, 200);
    }

    public function getSyslogDetail($id)
    {
        $syslog = SyslogHeaderModel::with(['details.parameter', 'user', 'device'])->find($id);

        if (!$syslog) {
            return response()->json(['error' => 'Syslog not found'], 404);
        }

        $data = [
            'id' => $syslog->id,
            'device_id' => $syslog->device_id,
            'device_name' => $syslog->device ? $syslog->device->device_name : null,
            'created_date' => $syslog->created_date,
            'category' => $syslog->category,
            'note' => $syslog->note,
            'linked_file' => $syslog->linked_file,
            'user_name' => $syslog->user ? $syslog->user->name : null,
            'created_at' => $syslog->created_at ? $syslog->created_at->format('Y-m-d H:i:s') : null,
            'details' => $syslog->details->map(function ($detail) {
                return [
                    'parameter_id' => $detail->parameter_id,
                    'parameter_name' => $detail->parameter ? $detail->parameter->parameter_label : null,
                    'description' => $detail->description,
                ];
            })->toArray(),
        ];

        return response()->json($data, 200);
    }


    public function deviceReport()
    {
        $userId = Auth::user()->id;
        $cacheKey = "user:{$userId}:device-report";
        $cacheTTL = 5; // Cache 5 menit

        // Check if cached
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            return view('user.report', array_merge($cachedData, [
                'accessCrud' => Auth::user()->level === 'advanced'
            ]));
        }

        $firstDevice = null;

        // STEP 1: Eager load dengan optimized relationships
        $userAccess = AccessModel::where('user_id', $userId)
            ->with(['category', 'device'])
            ->get();

        // Early exit jika tidak ada data
        if ($userAccess->isEmpty()) {
            $cacheData = [
                'deviceCategories' => [],
                'firstDeviceId' => null,
            ];
            Cache::put($cacheKey, $cacheData, now()->addMinutes($cacheTTL));
            return view('user.report', array_merge($cacheData, [
                'accessCrud' => Auth::user()->level === 'advanced'
            ]));
        }

        // STEP 2: Build response dengan collection methods (memory operations, no queries)
        $result = $userAccess->groupBy('category.category_name')
            ->map(function ($group) use (&$firstDevice) {
                $category = $group->first()->category;

                if (!$category) {
                    return null;
                }

                $devices = $group->filter(function ($access) {
                    return $access->device !== null;
                })->map(function ($access) use (&$firstDevice) {
                    $device = $access->device;
                    if ($firstDevice === null) {
                        $firstDevice = $device->device_id;
                    }
                    return [
                        'device_id' => $device->device_id
                    ];
                })->values();

                return !$devices->isEmpty() ? [
                    'device_category' => $category->category_name,
                    'devices' => $devices->toArray(),
                ] : null;
            })->filter()->values()->toArray();

        $cacheData = [
            'deviceCategories' => $result,
            'firstDeviceId' => $firstDevice,
        ];
        Cache::put($cacheKey, $cacheData, now()->addMinutes($cacheTTL));

        return view('user.report', array_merge($cacheData, [
            'accessCrud' => Auth::user()->level === 'advanced'
        ]));
    }

    public function getDeviceReport()
    {
        try {
            $userId = Auth::user()->id;
            // $cacheKey = "user:{$userId}:device-reports";
            // $cacheTTL = 2; // Cache 2 menit (data dapat berubah)

            // // Check if cached
            // if (Cache::has($cacheKey)) {
            //     return response()->json(Cache::get($cacheKey), 200);
            // }

            // Get all devices assigned to current user
            $userDevices = AccessModel::where('user_id', $userId)
                ->with('device')
                ->get()
                ->pluck('device')
                ->filter(); // Remove null devices

            if ($userDevices->isEmpty()) {
                $response = [
                    'success' => true,
                    'available_devices' => [],
                    'report' => [],
                ];
                // Cache::put($cacheKey, $response, now()->addMinutes($cacheTTL));
                return response()->json($response, 200);
            }

            // Get device IDs that already have reports
            $reportedDeviceIds = AutoReportModel::pluck('device_id')->toArray();

            // Filter available devices (not yet in reports)
            $availableDevices = $userDevices->filter(function ($device) use ($reportedDeviceIds) {
                return !in_array($device->device_id, $reportedDeviceIds);
            })->map(function ($device) {
                return [
                    'device_id' => $device->device_id,
                    'device_category' => $device->device_category,
                ];
            })->values()->toArray();

            // Get all reports for user's devices with device category
            $reports = AutoReportModel::whereIn('device_id', $userDevices->pluck('device_id'))
                ->with('device:device_id,device_category')
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'device_id' => $report->device_id,
                        'device_category' => $report->device ? $report->device->device_category : 'N/A',
                        'schedule' => ucfirst($report->schedule_report ?? 'N/A'),
                        'email' => $report->email_report ?? 'N/A',
                        'status' => $report->auto_report ?? 'Inactive',
                    ];
                });

            $response = [
                'success' => true,
                'available_devices' => $availableDevices,
                'report' => $reports,
            ];

            // Cache::put($cacheKey, $response, now()->addMinutes($cacheTTL));
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reports: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDeviceReportById($id)
    {
        try {
            // $cacheKey = "report:{$id}";
            // $cacheTTL = 5; // Cache 5 menit

            // // Check if cached
            // if (Cache::has($cacheKey)) {
            //     return response()->json(Cache::get($cacheKey), 200);
            // }

            $report = AutoReportModel::find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found'
                ], 404);
            }

            // Verify this report belongs to user's device
            $device = AccessModel::with('device')
                ->whereHas('device', function ($query) use ($report) {
                    $query->where('device_id', $report->device_id);
                })
                ->where('user_id', Auth::user()->id)
                ->first()
                ?->device;

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $response = [
                'success' => true,
                'report' => [
                    'id' => $report->id,
                    'device_id' => $report->device_id,
                    'schedule' => $report->schedule_report,
                    'email' => $report->email_report,
                    'status' => $report->auto_report,
                ]
            ];

            // Cache::put($cacheKey, $response, now()->addMinutes($cacheTTL));
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveDeviceReport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'device_id' => 'required|string|exists:tbl_device,device_id',
                'schedule' => 'required|string|in:daily,weekly,monthly',
                'email' => 'required|email',
                'status' => 'required|string|in:Active,Inactive',
            ]);


            $deviceId = $validated['device_id'];
            // Verify device belongs to user
            $device = AccessModel::with('device')
                ->whereHas('device', function ($query) use ($deviceId) {
                    $query->where('device_id', $deviceId);
                })
                ->where('user_id', Auth::user()->id)
                ->first()
                ?->device;



            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or unauthorized'
                ], 403);
            }

            // Check if report already exists for this device
            $existingReport = AutoReportModel::where('device_id', $validated['device_id'])->first();
            if ($existingReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report already exists for this device'
                ], 400);
            }

            // Create new report
            $report = AutoReportModel::create([
                'device_id' => $validated['device_id'],
                'schedule_report' => $validated['schedule'],
                'email_report' => $validated['email'],
                'auto_report' => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report created successfully',
                'report' => $report
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDeviceReport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'id' => 'required|integer|exists:tbl_auto_report,id',
                'device_id' => 'required|string|exists:tbl_device,device_id',
                'schedule' => 'required|string|in:daily,weekly,monthly',
                'email' => 'required|email',
                'status' => 'required|string|in:Active,Inactive',
            ]);

            $report = AutoReportModel::find($validated['id']);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found'
                ], 404);
            }

            // Verify device belongs to user
            $device = AccessModel::with('device')
                ->whereHas('device', function ($query) use ($validated) {
                    $query->where('device_id', $validated['device_id']);
                })
                ->where('user_id', Auth::user()->id)
                ->first()
                ?->device;

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Update report
            $report->update([
                'device_id' => $validated['device_id'],
                'schedule_report' => $validated['schedule'],
                'email_report' => $validated['email'],
                'auto_report' => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully',
                'report' => $report
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteDeviceReport($id)
    {
        try {
            $report = AutoReportModel::find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found'
                ], 404);
            }

            // Verify device belongs to user
            $device = AccessModel::with('device')
                ->whereHas('device', function ($query) use ($report) {
                    $query->where('device_id', $report->device_id);
                })
                ->where('user_id', Auth::user()->id)
                ->first()
                ?->device;

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $report->delete();

            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getTableDeviceReport(Request $request)
    {
        try {
            // Validate request with pagination support
            $validated = $request->validate([
                'device_id' => 'required|string|exists:tbl_device,device_id',
                'start_date' => 'required|string',
                'end_date'   => 'required|string',
                'page'       => 'nullable|integer|min:1',
            ]);

            $deviceId  = $validated['device_id'];
            $startDate = $validated['start_date'];
            $endDate   = $validated['end_date'];
            $page      = (int) ($validated['page'] ?? 1);
            $perPage   = 720;

            // Parse datetimes — supports both "Y-m-d H:i" and "Y-m-d" formats
            $startDateObj = Carbon::parse($startDate);
            $endDateObj   = Carbon::parse($endDate);

            if ($startDateObj->greaterThan($endDateObj)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Start date/time must be before or equal to end date/time.'
                ], 422);
            }

            // Convert to Unix timestamp; add 59s to end time to include the full last minute
            $startDateUnix = $startDateObj->timestamp;
            $endDateUnix   = (clone $endDateObj)->addSeconds(59)->timestamp;

            // Verify device belongs to user
            $device = DeviceModel::where('device_id', $deviceId)
                ->whereIn('id', function ($query) {
                    $query->select('device_id')
                        ->from('tbl_access')
                        ->where('user_id', Auth::id());
                })
                ->select('device_id', 'device_category')
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or unauthorized'
                ], 403);
            }

            // Get active sensors with parameter details
            $sensors = SensorModel::where('tbl_sensor.device_id', $deviceId)
                ->where('tbl_sensor.status', 'active')
                ->join('tbl_parameter', 'tbl_sensor.parameter_name', '=', 'tbl_parameter.parameter_name')
                ->select(
                    'tbl_sensor.parameter_name',
                    'tbl_parameter.parameter_label',
                    'tbl_sensor.sensor_unit'
                )
                ->orderBy('tbl_sensor.id', 'asc')
                ->get();

            if ($sensors->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active sensors found for this device'
                ], 404);
            }

            // Build parameter info and list
            $parameterInfo = [];
            $parameters    = [];
            foreach ($sensors as $sensor) {
                $parameters[]    = $sensor->parameter_name;
                $parameterInfo[] = [
                    'parameter_name'  => $sensor->parameter_name,
                    'parameter_label' => $sensor->parameter_label ?? $sensor->parameter_name,
                    'parameter_unit'  => $sensor->sensor_unit ?? ''
                ];
            }

            // Cache keys (v2 prefix avoids collision with old single-page cache)
            $baseCacheKey  = "device:{$deviceId}:table-report-v2:{$startDate}:{$endDate}";
            $countCacheKey = "{$baseCacheKey}:count";
            $pageCacheKey  = "{$baseCacheKey}:page:{$page}";
            $cacheTTL      = 5; // minutes

            // Return cached page if available
            if (Cache::has($pageCacheKey)) {
                return response()->json(Cache::get($pageCacheKey), 200);
            }

            // Get or cache total count of distinct timestamp_minutes
            if (Cache::has($countCacheKey)) {
                $totalRecords = Cache::get($countCacheKey);
            } else {
                $totalRecords = (int) DB::table('tbl_data')
                    ->selectRaw("COUNT(DISTINCT DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i')) as total")
                    ->where('device_id', $deviceId)
                    ->whereIn('parameter_name', $parameters)
                    ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                    ->value('total');
                Cache::put($countCacheKey, $totalRecords, now()->addMinutes($cacheTTL));
            }

            if ($totalRecords === 0) {
                return response()->json([
                    'success'        => true,
                    'message'        => 'No data found for the selected date range',
                    'device_id'      => $deviceId,
                    'device_category'=> $device->device_category,
                    'parameters'     => $parameterInfo,
                    'data'           => [],
                    'total_records'  => 0,
                    'current_page'   => 1,
                    'per_page'       => $perPage,
                    'total_pages'    => 0,
                    'date_range'     => ['start' => $startDate, 'end' => $endDate]
                ], 200);
            }

            $totalPages = (int) ceil($totalRecords / $perPage);
            $page       = min($page, $totalPages); // clamp to valid range

            // Step 1: Get the distinct timestamp_minutes for the requested page
            $pageTimestamps = DB::table('tbl_data')
                ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i') as ts_minute")
                ->where('device_id', $deviceId)
                ->whereIn('parameter_name', $parameters)
                ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                ->groupBy('ts_minute')
                ->orderBy('ts_minute', 'asc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->pluck('ts_minute')
                ->toArray();

            if (empty($pageTimestamps)) {
                return response()->json([
                    'success'        => true,
                    'message'        => 'No data found for this page',
                    'device_id'      => $deviceId,
                    'device_category'=> $device->device_category,
                    'parameters'     => $parameterInfo,
                    'data'           => [],
                    'total_records'  => $totalRecords,
                    'current_page'   => $page,
                    'per_page'       => $perPage,
                    'total_pages'    => $totalPages,
                    'date_range'     => ['start' => $startDate, 'end' => $endDate]
                ], 200);
            }

            // Step 2: Fetch all sensor values for the page's timestamps
            $data = DataModel::select(
                DB::raw("DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i') as timestamp_minute"),
                'parameter_name',
                'value',
                'timestamp'
            )
                ->where('device_id', $deviceId)
                ->whereIn('parameter_name', $parameters)
                ->whereIn(
                    DB::raw("DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i')"),
                    $pageTimestamps
                )
                ->orderBy('timestamp', 'asc')
                ->get();

            // Group data by timestamp_minute
            $groupedData = [];
            foreach ($data as $item) {
                $ts = $item->timestamp_minute;
                if (!isset($groupedData[$ts])) {
                    $groupedData[$ts] = ['recorded_at' => $item->timestamp, 'values' => []];
                }
                $groupedData[$ts]['values'][$item->parameter_name] = round(floatval($item->value), 2);
            }

            // Format into table rows; row numbers continue across pages
            $tableData = [];
            $no        = (($page - 1) * $perPage) + 1;
            foreach ($groupedData as $group) {
                $dateTime = Carbon::createFromTimestamp($group['recorded_at'], 'UTC')
                    ->setTimezone(config('app.timezone'));
                $row = [
                    'no'          => $no++,
                    'date'        => $dateTime->format('Y-m-d'),
                    'time'        => $dateTime->format('H:i'),
                    'recorded_at' => $dateTime->format('Y-m-d H:i:s')
                ];
                foreach ($parameters as $parameter) {
                    $row[$parameter] = $group['values'][$parameter] ?? null;
                }
                $tableData[] = $row;
            }

            $response = [
                'success'        => true,
                'device_id'      => $deviceId,
                'device_category'=> $device->device_category,
                'parameters'     => $parameterInfo,
                'data'           => $tableData,
                'total_records'  => $totalRecords,
                'current_page'   => $page,
                'per_page'       => $perPage,
                'total_pages'    => $totalPages,
                'date_range'     => ['start' => $startDate, 'end' => $endDate]
            ];

            Cache::put($pageCacheKey, $response, now()->addMinutes($cacheTTL));

            return response()->json($response, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Report validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Report fetch error', [
                'device_id' => $request->input('device_id'),
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report data',
                'debug'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Return the expected row count for an export (fast COUNT query).
     * Used by the frontend to show warnings before downloading large files.
     */
    public function getExportRecordCount(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id'  => 'required|string|exists:tbl_device,device_id',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $deviceId  = $validated['device_id'];
            $startDate = $validated['start_date'];
            $endDate   = $validated['end_date'];

            // Verify access
            $hasAccess = DeviceModel::where('device_id', $deviceId)
                ->whereIn('id', function ($q) {
                    $q->select('device_id')->from('tbl_access')->where('user_id', Auth::id());
                })->exists();

            if (!$hasAccess) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $startDateUnix = Carbon::parse($startDate)->startOfDay()->timestamp;
            $endDateUnix   = Carbon::parse($endDate)->endOfDay()->timestamp;

            // Count distinct minute-grouped timestamps = expected export rows
            $count = DataModel::where('device_id', $deviceId)
                ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                ->distinct()
                ->count(DB::raw("DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i')"));

            return response()->json(['success' => true, 'count' => (int) $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate and stream an Excel report.
     * Uses cursor()-based query to stay memory-efficient for large datasets,
     * writes to a temp file, then streams the file via response()->download().
     */
    public function exportReportExcel(Request $request)
    {
        // Raise limits – large exports can take time and memory
        ini_set('memory_limit', '512M');
        set_time_limit(600);

        try {
            $validated = $request->validate([
                'device_id'  => 'required|string|exists:tbl_device,device_id',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $deviceId  = $validated['device_id'];
            $startDate = $validated['start_date'];
            $endDate   = $validated['end_date'];

            // Verify device belongs to authenticated user
            $device = DeviceModel::where('device_id', $deviceId)
                ->whereIn('id', function ($query) {
                    $query->select('device_id')
                        ->from('tbl_access')
                        ->where('user_id', Auth::id());
                })
                ->select('device_id', 'device_category')
                ->first();

            if (!$device) {
                return response()->json(['success' => false, 'message' => 'Device not found or unauthorized'], 403);
            }

            // Active sensors for this device
            $sensors = SensorModel::where('tbl_sensor.device_id', $deviceId)
                ->where('tbl_sensor.status', 'active')
                ->join('tbl_parameter', 'tbl_sensor.parameter_name', '=', 'tbl_parameter.parameter_name')
                ->select('tbl_sensor.parameter_name', 'tbl_parameter.parameter_label', 'tbl_sensor.sensor_unit')
                ->orderBy('tbl_sensor.id', 'asc')
                ->get();

            if ($sensors->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active sensors found for this device'], 404);
            }

            $parameters    = [];
            $parameterInfo = [];
            foreach ($sensors as $sensor) {
                $parameters[]    = $sensor->parameter_name;
                $parameterInfo[] = [
                    'parameter_name'  => $sensor->parameter_name,
                    'parameter_label' => $sensor->parameter_label ?? $sensor->parameter_name,
                    'parameter_unit'  => $sensor->sensor_unit ?? '',
                ];
            }

            $startDateUnix = Carbon::parse($startDate)->startOfDay()->timestamp;
            $endDateUnix   = Carbon::parse($endDate)->endOfDay()->timestamp;

            // ── Build Spreadsheet ────────────────────────────────────────
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            $spreadsheet->getProperties()
                ->setCreator('Data Center System')
                ->setTitle("Report {$deviceId}")
                ->setSubject('Device Report')
                ->setDescription("Report for device {$deviceId} from {$startDate} to {$endDate}");

            $totalColumns = 3 + count($parameterInfo); // No + Date + Time + params
            $lastCol      = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);

            $row = 1;

            // Logo
            $logoPath = public_path('assets/img/HasSolution.png');
            if (file_exists($logoPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo')->setDescription('Company Logo')
                    ->setPath($logoPath)->setHeight(60)
                    ->setCoordinates('A1')->setOffsetX(10)->setOffsetY(5)
                    ->setWorksheet($sheet);
                $sheet->getRowDimension(1)->setRowHeight(50);
                $row += 2;
            }

            // Title row
            $sheet->setCellValue("A{$row}", 'Device Report');
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
            $row++;

            // Info rows
            $infoData = [
                'Device ID:'    => $device->device_id,
                'Category:'     => $device->device_category,
                'Parameters:'   => implode(', ', array_column($parameterInfo, 'parameter_label')),
                'Date Range:'   => "{$startDate} to {$endDate}",
                'Generated On:' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            // Reserve a row for Total Records (filled after data loop)
            $totalRecordsRow = null;
            foreach ($infoData as $label => $value) {
                $sheet->setCellValue("A{$row}", $label);
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->setCellValue("B{$row}", $value);
                if ($label === 'Parameters:') {
                    $sheet->mergeCells("B{$row}:{$lastCol}{$row}");
                }
                $row++;
            }
            // Insert "Total Records" placeholder (value updated after cursor loop)
            $sheet->setCellValue("A{$row}", 'Total Records:');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$row}", '…');
            $totalRecordsRow = $row;
            $row++;

            $tableHeaderRow = $row;

            // Table column headers
            $colIdx = 1;
            foreach (['No', 'Date', 'Time'] as $hdr) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue("{$colLetter}{$row}", $hdr);
                $sheet->getStyle("{$colLetter}{$row}")->getFont()->setBold(true);
                $colIdx++;
            }
            foreach ($parameterInfo as $param) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $hdrText   = $param['parameter_label'] . (!empty($param['parameter_unit']) ? ' (' . $param['parameter_unit'] . ')' : '');
                $sheet->setCellValue("{$colLetter}{$row}", $hdrText);
                $sheet->getStyle("{$colLetter}{$row}")->getFont()->setBold(true);
                $colIdx++;
            }
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9D9D9');
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setHorizontal('center');

            // ── Stream data via cursor() ─────────────────────────────────
            // cursor() returns rows one at a time – O(1) PHP memory regardless of dataset size.
            // We group consecutive rows that share the same minute-bucket on the fly.
            $dataRow      = $row + 1;
            $no           = 1;
            $prevMin      = null;
            $currentGroup = null;

            $cursor = DataModel::select(
                DB::raw("DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i') as timestamp_minute"),
                'parameter_name',
                'value',
                'timestamp'
            )
                ->where('device_id', $deviceId)
                ->whereIn('parameter_name', $parameters)
                ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                ->orderBy('timestamp', 'asc')
                ->cursor();

            foreach ($cursor as $item) {
                if ($prevMin !== $item->timestamp_minute) {
                    if ($currentGroup !== null) {
                        $this->writeExcelDataRow($sheet, $dataRow++, $no++, $currentGroup, $parameters);
                    }
                    $prevMin      = $item->timestamp_minute;
                    $currentGroup = ['timestamp' => $item->timestamp, 'values' => []];
                }
                $currentGroup['values'][$item->parameter_name] = round(floatval($item->value), 2);
            }
            if ($currentGroup !== null) {
                $this->writeExcelDataRow($sheet, $dataRow++, $no++, $currentGroup, $parameters);
            }

            $totalRecords = $no - 1;
            // Fill in the previously reserved Total Records cell
            $sheet->setCellValue("B{$totalRecordsRow}", $totalRecords);

            // ── Post-loop formatting ─────────────────────────────────────
            // Auto-size columns
            for ($c = 1; $c <= $totalColumns; $c++) {
                $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
            }

            // Borders on the full table range
            $lastDataRow = $dataRow - 1;
            if ($lastDataRow >= $tableHeaderRow) {
                $sheet->getStyle("A{$tableHeaderRow}:{$lastCol}{$lastDataRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            // Freeze header row
            $sheet->freezePane('A' . ($tableHeaderRow + 1));

            // ── Save to temp file and stream ─────────────────────────────
            $tmpDir = storage_path('app/temp');
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }
            $tmpFile = $tmpDir . '/excel_' . uniqid() . '.xlsx';
            $writer  = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($tmpFile);
            unset($spreadsheet, $writer); // Free memory before streaming

            $filename = "Report_{$deviceId}_{$startDate}_to_{$endDate}.xlsx";

            return response()->download($tmpFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to generate Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Write a single grouped data row into the spreadsheet.
     * Kept as a separate method to keep the main export method readable
     * and to avoid per-call style overhead inside the hot loop.
     */
    private function writeExcelDataRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int   $dataRow,
        int   $no,
        array $group,
        array $parameters
    ): void {
        $tz       = config('app.timezone', 'UTC');
        $dateTime = Carbon::createFromTimestamp($group['timestamp'], 'UTC')->setTimezone($tz);

        $colIdx = 1;
        foreach ([$no, $dateTime->format('Y-m-d'), $dateTime->format('H:i:s')] as $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$colLetter}{$dataRow}", $val);
        }
        foreach ($parameters as $paramName) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx++);
            $sheet->setCellValue("{$colLetter}{$dataRow}", $group['values'][$paramName] ?? '');
        }
    }

    /**
     * Return per-parameter statistical summary (avg / max / min with timestamps)
     * plus hourly averages (0-23) for chart rendering.
     */
    public function getReportSummary(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id'  => 'required|string|exists:tbl_device,device_id',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $deviceId  = $validated['device_id'];
            $startDate = $validated['start_date'];
            $endDate   = $validated['end_date'];

            // Verify access
            $device = DeviceModel::where('device_id', $deviceId)
                ->whereIn('id', function ($q) {
                    $q->select('device_id')->from('tbl_access')->where('user_id', Auth::id());
                })
                ->select('device_id', 'device_category')
                ->first();

            if (!$device) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Active sensors ordered by sensor position
            $sensors = SensorModel::where('tbl_sensor.device_id', $deviceId)
                ->where('tbl_sensor.status', 'active')
                ->join('tbl_parameter', 'tbl_sensor.parameter_name', '=', 'tbl_parameter.parameter_name')
                ->select('tbl_sensor.parameter_name', 'tbl_parameter.parameter_label', 'tbl_sensor.sensor_unit')
                ->orderBy('tbl_sensor.id', 'asc')
                ->get();

            if ($sensors->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active sensors found'], 404);
            }

            $parameters    = $sensors->pluck('parameter_name')->toArray();
            $startDateUnix = Carbon::parse($startDate)->startOfDay()->timestamp;
            $endDateUnix   = Carbon::parse($endDate)->endOfDay()->timestamp;
            $tz            = config('app.timezone', 'UTC');

            // ── Aggregates (avg / max / min) in one query ────────────────
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

            // ── Build stats array with max/min timestamps ─────────────────
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

            // ── Hourly averages per parameter for chart rendering (24-slot profile) ──
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
                    $hourlyCharts[$row->parameter_name][(int) $row->hour] = floatval($row->avg_value);
                }
            }

            // ── Flexible datetime-grouped hourly data for table display ───────────
            // Groups by hour-bucket (floor to hour) across the full date range
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
                        'values' => [],
                    ];
                }
                $hourlyTableMap[$hts]['values'][$row->parameter_name] = floatval($row->avg_value);
            }
            $hourlyTableData = array_values($hourlyTableMap);

            return response()->json([
                'success'         => true,
                'device_id'       => $deviceId,
                'device_category' => $device->device_category,
                'date_range'      => ['start' => $startDate, 'end' => $endDate],
                'stats'           => $stats,
                'hourly_charts'   => $hourlyCharts,
                'hourly_table'    => $hourlyTableData,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getReportData($deviceId, $startDate, $endDate)
    {
        try {
            // Cache key for report data (includes date range)
            $cacheKey = "device:{$deviceId}:report:{$startDate}:{$endDate}";
            $cacheTTL = 5; // 5 minutes - appropriate for report data that may be regenerated

            // Check if cached
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Verify device belongs to user - optimized query
            $device = DeviceModel::where('device_id', $deviceId)
                ->whereIn('id', function ($query) {
                    $query->select('device_id')
                        ->from('tbl_access')
                        ->where('user_id', Auth::id());
                })
                ->select('device_id', 'device_category')
                ->first();

            if (!$device) {
                return [
                    'success' => false,
                    'message' => 'Device not found or unauthorized'
                ];
            }

            // Get sensors with parameter details using optimized join
            $sensors = SensorModel::where('tbl_sensor.device_id', $deviceId)
                ->where('tbl_sensor.status', 'active')
                ->join('tbl_parameter', 'tbl_sensor.parameter_name', '=', 'tbl_parameter.parameter_name')
                ->select(
                    'tbl_sensor.parameter_name',
                    'tbl_parameter.parameter_label',
                    'tbl_sensor.sensor_unit'
                )
                ->orderBy('tbl_sensor.id', 'asc')
                ->get();

            if ($sensors->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No active sensors found for this device'
                ];
            }

            // Build parameter info array and parameter list in one loop
            $parameterInfo = [];
            $parameters = [];
            foreach ($sensors as $sensor) {
                $parameters[] = $sensor->parameter_name;
                $parameterInfo[] = [
                    'parameter_name' => $sensor->parameter_name,
                    'parameter_label' => $sensor->parameter_label ?? $sensor->parameter_name,
                    'parameter_unit' => $sensor->sensor_unit ?? ''
                ];
            }

            //ubah menjadi tanggal menjadi format unix
            $startDateUnix = Carbon::parse($startDate)->startOfDay()->timestamp;
            $endDateUnix = Carbon::parse($endDate)->endOfDay()->timestamp;

            // Get data with optimized query - select specific columns and filter by parameters
            $data = DataModel::select(
                DB::raw("DATE_FORMAT(FROM_UNIXTIME(`timestamp`), '%Y-%m-%d %H:%i') as timestamp_minute"),
                'parameter_name',
                'value',
                'timestamp'
            )
                ->where('device_id', $deviceId)
                ->whereIn('parameter_name', $parameters)
                ->whereBetween('timestamp', [$startDateUnix, $endDateUnix])
                ->orderBy('timestamp', 'asc')
                ->get();


            if ($data->isEmpty()) {
                $reportData = [
                    'success' => true,
                    'message' => 'No data found for the selected date range',
                    'device_id' => $deviceId,
                    'device_category' => $device->device_category,
                    'parameters' => $parameterInfo,
                    'data' => [],
                    'total_records' => 0,
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ];
                // Store in cache even for empty results
                Cache::put($cacheKey, $reportData, now()->addMinutes($cacheTTL));
                return $reportData;
            }

            // Group data by timestamp more efficiently
            $groupedData = [];
            foreach ($data as $item) {
                $timestamp = $item->timestamp_minute;
                if (!isset($groupedData[$timestamp])) {
                    $groupedData[$timestamp] = [
                        'recorded_at' => $item->timestamp,
                        'values' => []
                    ];
                }
                $groupedData[$timestamp]['values'][$item->parameter_name] = round(floatval($item->value), 2);
            }

            // Format data into table
            $tableData = [];
            $no = 1;
            foreach ($groupedData as $timestamp => $group) {
                $dateTime = Carbon::createFromTimestamp($group['recorded_at'], 'UTC')
                    ->setTimezone(config('app.timezone'));
                $row = [
                    'no' => $no++,
                    'date' => $dateTime->format('Y-m-d'),
                    'time' => $dateTime->format('H:i:s'),
                    'recorded_at' => $dateTime->format('Y-m-d H:i:s')
                ];

                // Add parameter values
                foreach ($parameters as $parameter) {
                    $row[$parameter] = $group['values'][$parameter] ?? null;
                }

                $tableData[] = $row;
            }

            $reportData = [
                'success' => true,
                'device_id' => $deviceId,
                'device_category' => $device->device_category,
                'parameters' => $parameterInfo,
                'data' => $tableData,
                'total_records' => count($tableData),
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ];

            // Store in cache
            Cache::put($cacheKey, $reportData, now()->addMinutes($cacheTTL));

            return $reportData;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch report data: ' . $e->getMessage()
            ];
        }
    }

    public function settings()
    {
        $userId = Auth::user()->id;
        $cacheKey = "user:{$userId}:settings";
        $cacheTTL = 10; // Cache 10 menit (data jarang berubah)

        // Check if cached
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return view('user.settings', array_merge($cached, [
                'accessCrud' => Auth::user()->level === 'advanced'
            ]));
        }

        $devices = AccessModel::with('device.sensors.parameter')
            ->where('user_id', $userId)
            ->whereHas('device.sensors', function ($query) {
                $query->where('status', 'active');
            })
            ->get()
            ->map(function ($access) {
                return $access->device;
            })
            ->filter(); // Remove null devices

        $profile = User::find($userId);

        $cacheData = [
            'devices' => $devices,
            'profile' => $profile,
        ];
        Cache::put($cacheKey, $cacheData, now()->addMinutes($cacheTTL));

        return view('user.settings', array_merge($cacheData, [
            'accessCrud' => Auth::user()->level === 'advanced'
        ]));
    }

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:tbl_user,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:tbl_user,email,' . $request->id,
                'password' => 'nullable|string|min:6',
            ]);

            $user = User::find($request->id);

            // Check if user is authorized to update this profile
            if ($user->id !== Auth::user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::find(Auth::user()->id);

            if (!password_verify($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ], 400);
            }

            $user->password = bcrypt($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateParameterAlerts(Request $request)
    {
        try {
            $sensors = $request->input('sensors', []);

            if (empty($sensors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sensor data provided.'
                ], 400);
            }

            $userId = Auth::user()->id;
            $updatedCount = 0;
            $errors = [];

            foreach ($sensors as $sensorId => $data) {
                // Skip if no alert value provided or empty
                if (!isset($data['parameter_indicator_alert']) || $data['parameter_indicator_alert'] === '') {
                    continue;
                }

                // // Verify user has access to this sensor through device access
                $sensor = SensorModel::with('device.access')
                    ->find($sensorId);

                // if (!$sensor) {
                //     $errors[] = "Sensor ID {$sensorId} not found.";
                //     continue;
                // }

                // // Check if user has access to this sensor's device
                // $hasAccess = $sensor->device->access()
                //     ->where('user_id', $userId)
                //     ->exists();

                // if (!$hasAccess) {
                //     $errors[] = "Unauthorized access to sensor ID {$sensorId}.";
                //     continue;
                // }

                // Validate alert value is within range
                $alertValue = floatval($data['parameter_indicator_alert']);

                // Check minimum value
                if ($sensor->parameter_indicator_min !== null && $alertValue < $sensor->parameter_indicator_min) {
                    $parameterLabel = $sensor->parameter->parameter_label ?? $sensor->parameter_name;
                    $errors[] = "Alert value for {$parameterLabel} ({$alertValue}) cannot be less than minimum value ({$sensor->parameter_indicator_min}).";
                    continue;
                }

                // Check maximum value
                if ($sensor->parameter_indicator_max !== null && $alertValue > $sensor->parameter_indicator_max) {
                    $parameterLabel = $sensor->parameter->parameter_label ?? $sensor->parameter_name;
                    $errors[] = "Alert value for {$parameterLabel} ({$alertValue}) cannot be greater than maximum value ({$sensor->parameter_indicator_max}).";
                    continue;
                }

                // Update sensor
                $sensor->parameter_indicator_alert = $alertValue;
                $sensor->save();
                $updatedCount++;
            }

            if ($updatedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "{$updatedCount} parameter alert(s) updated successfully.",
                    'errors' => $errors
                ]);
            } else {
                if (!empty($errors)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No parameters were updated due to validation errors.',
                        'errors' => $errors
                    ], 400);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No parameters were updated. Please enter alert values.',
                        'errors' => []
                    ], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update parameter alerts: ' . $e->getMessage()
            ], 500);
        }
    }






    //view Guidance
    public function getGuidance()
    {
        $guidances = GuidanceModel::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'content' => $item->content,
                'image_path' => $item->image_path ? 'storage/' . $item->image_path : null,
                'link_path' => $item->link_path,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $guidances
        ], 200);
    }


    //ambil total notif logs belum dibaca user
    public function getUnreadLogsCount()
    {
        $unreadCount = AccessModel::with(['device.logs' => function ($query) {
            $query->where('is_read_user', false);
        }])
            ->where('user_id', Auth::user()->id)
            ->get()
            ->flatMap(function ($access) {
                return $access->device->logs;
            })
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ], 200);
    }


    //view Logs
    public function getLogsData(Request $request)
    {
        try {
            $userId = Auth::user()->id;
            $deviceId = $request->query('device_id');
            $category = $request->query('category');
            $startDateTime = $request->query('start_date');
            $endDateTime = $request->query('end_date');
            $status = $request->query('status');

            // Build cache key with query parameters
            $cacheKey = "user:{$userId}:logs:" . md5(json_encode([
                'device_id' => $deviceId,
                'category' => $category,
                'start_date' => $startDateTime,
                'end_date' => $endDateTime,
                'status' => $status,
            ]));
            $cacheTTL = 1; // Cache 1 menit untuk logs

            // Check if cached
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey), 200);
            }

            // Get all logs for devices accessible by user
            $query = AccessModel::with(['device.logs' => function ($query) use ($category, $startDateTime, $endDateTime, $status) {
                $query->orderBy('log_date', 'desc');

                if ($category) {
                    $query->where('category', $category);
                }

                if ($startDateTime && $endDateTime) {
                    $query->whereBetween('log_date', [$startDateTime, $endDateTime]);
                }

                if ($status) {
                    $query->where('action', $status);
                }
            }])
                ->where('user_id', Auth::user()->id);

            if ($deviceId) {
                $query->whereHas('device', function ($q) use ($deviceId) {
                    $q->where('device_id', $deviceId);
                });
            }

            $logs = $query->get()
                ->flatMap(function ($access) {
                    return $access->device->logs->map(function ($log) use ($access) {
                        return [
                            'id' => $log->id,
                            'device_id' => $access->device->device_id,
                            'device_name' => $access->device->device_name,
                            'datetime' => $log->created_at->toDateTimeString(),
                            'category' => $log->category,
                            'message' => $log->message,
                            'action' => $log->action,
                            'status' => $log->action,
                            'is_read' => $log->is_read_user ?? false,
                        ];
                    });
                })
                ->sortByDesc('datetime')
                ->values();

            $response = [
                'success' => true,
                'data' => $logs,
            ];

            Cache::put($cacheKey, $response, now()->addMinutes($cacheTTL));
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch logs: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get user devices for filter
    public function getUserDevices()
    {
        try {
            $userId = Auth::user()->id;
            $cacheKey = "user:{$userId}:devices-list";
            $cacheTTL = 10; // Cache 10 menit

            // Check if cached
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey), 200);
            }

            $devices = AccessModel::with('device')
                ->where('user_id', $userId)
                ->get()
                ->map(function ($access) {
                    return [
                        'device_id' => $access->device->device_id,
                        'device_name' => $access->device->device_name,
                        'category' => $access->device->device_category,
                    ];
                })
                ->unique('device_id')
                ->values();

            $response = [
                'success' => true,
                'data' => $devices,
            ];

            Cache::put($cacheKey, $response, now()->addMinutes($cacheTTL));
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch devices: ' . $e->getMessage()
            ], 500);
        }
    }

    // Mark logs as read when modal closes
    public function markLogsAsRead()
    {
        try {
            // Get all device IDs accessible by user
            $deviceIds = AccessModel::with('device')
                ->where('user_id', Auth::user()->id)
                ->get()
                ->pluck('device.device_id')
                ->filter(); // Remove null device_ids

            // Update all unread logs for these devices
            LogsModel::whereIn('device_id', $deviceIds)
                ->where('is_read_user', false)
                ->update(['is_read_user' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All logs marked as read',
                'device' => $deviceIds,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark logs as read: ' . $e->getMessage()
            ], 500);
        }
    }
}
