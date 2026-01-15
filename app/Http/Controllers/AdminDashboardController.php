<?php

namespace App\Http\Controllers;

use App\Models\AccessModel;
use Illuminate\Http\Request;
use App\Models\CategoryModel;
use App\Models\DeviceModel;
use App\Models\DataModel;
use App\Models\ParameterModel;
use App\Models\SensorModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
      
        if(Auth::user()->level == 'master'){
            // Jika master, tampilkan semua device categories dan devices
            $deviceCategories = CategoryModel::with(['devices' => function ($query) {
                $query->orderBy('device_id', 'asc');
            }])
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
                            'status' => $this->DeviceStatus($device->device_ip),
                        ];
                    })->toArray(),
                ];
            })->toArray();

        }else{

            // Selain master, tampilkan device categories dan devices yang usernya user assigned ke user yang login
            $deviceCategories = CategoryModel::with(['devices' => function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id)
                      ->orderBy('device_id', 'asc');
            }])
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
                            'status' => $this->DeviceStatus($device->device_ip),
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }

        return view('admin.manage_dashboard', [
            'deviceCategories' => $deviceCategories
        ]);
       

    }


    public function DeviceStatus($deviceIp)
    {
        // lakukan ping ke IP address tersebut
        $ipAddress = $deviceIp;
        $pingResult = exec("ping -c 1 " . escapeshellarg($ipAddress), $output, $status);
        if ($status === 0) {
            $statusMessage = "Online";
        } else {
            $statusMessage = "Offline";
        }
        return $statusMessage;
    }

    public function getMapsDashboard($deviceId)
    {
        $device = DeviceModel::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        return response()->json([
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'device_icon' => $device->category ? $device->category->category_icon : null,
            'device_category' => $device->device_category,
            'latitude' => $device->latitude,
            'longitude' => $device->longitude,
            'status' => $this->DeviceStatus($device->device_ip),

        ], 200);
    }


    public function progressBar($deviceId)
    {

        $data = ParameterModel::whereIn('parameter_name', function ($query) use ($deviceId) {
            $query->select('parameter_name')
                ->from('tbl_sensor')
                ->where('device_id', $deviceId);
        })->get()
            ->map(function ($parameter) use ($deviceId) {
                $sensor = SensorModel::where('device_id', $deviceId)
                    ->where('parameter_name', $parameter->parameter_name)
                    ->first();
                $latestData = DataModel::where('device_id', $deviceId)
                    ->where('parameter_name', $parameter->parameter_name)
                    ->orderBy('timestamp', 'desc')
                    ->first();

                return [
                    'parameter_label' => $parameter->parameter_label,
                    'parameter_name' => $sensor->parameter_name,
                    'latest_value' => $latestData ? $latestData->value : null,
                    'recorded_at' => $latestData ? $this->unixToDateTime($latestData->timestamp)->format('Y-m-d H:i:s') : null,
                    'parameter_indicator_min' => $sensor->parameter_indicator_min,
                    'parameter_indicator_max' => $sensor->parameter_indicator_max,
                    'sensor_unit' => $sensor->sensor_unit,

                ];
            });
        return response()->json($data);
    }

    public function lineChartData(Request $request, $deviceId)
    {
        $parameter = $request->query('parameter');

        // Validate parameter
        if (!$parameter) {
            return response()->json(['error' => 'Parameter is required'], 400);
        }

        // Get sensor unit
        $sensor = SensorModel::where('device_id', $deviceId)
            ->where('parameter_name', $parameter)
            ->first();

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

        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'parameter_label' => $sensor ? $sensor->parameter->parameter_label : $sensor->parameter_name,
            'unit' => $unit,
        ]);
    }


    public function barChartData(Request $request, $deviceId)
    {
        $parameter = $request->query('parameter');

        // Validate parameter
        if (!$parameter) {
            return response()->json(['error' => 'Parameter is required'], 400);
        }

        // Get sensor unit
        $sensor = SensorModel::where('device_id', $deviceId)
            ->where('parameter_name', $parameter)
            ->first();

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
        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'parameter_label' => $sensor ? $sensor->parameter->parameter_label : $sensor->parameter_name,
            'unit' => $unit
        ]);
    }


    public function windroseData($deviceId)
    {
        try {

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

            return response()->json([
                "timestamps" => $timestamps,
                "wspeed" => $wspeed,
                "wdir" => $wdir
            ]);
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
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // ubah format tanggal ke unix timestamp
            $startDate = Carbon::parse($startDate)->timestamp;
            $endDate = Carbon::parse($endDate)->timestamp;

            if (!$parameterName || !$startDate || !$endDate) {
                return response()->json([
                    'labels' => [],
                    'values' => [],
                    'unit' => '',
                    'error' => 'Missing required parameters'
                ], 400);
            }

            // Query data tanpa aggregation - ambil semua data points
            $data = DataModel::where('device_id', $deviceId)
                ->where('parameter_name', $parameterName)
                ->whereBetween('timestamp', [$startDate, $endDate])
                ->orderBy('timestamp', 'asc')
                ->get();
            // Get sensor unit
            $sensor = SensorModel::where('device_id', $deviceId)
                ->where('parameter_name', $parameterName)
                ->first();
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
            return response()->json([
                'labels' => $labels,
                'values' => $values,
                'parameter_label' => $sensor ? $sensor->parameter->parameter_label : $sensor->parameter_name,
                'unit' => $unit
            ]);
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
