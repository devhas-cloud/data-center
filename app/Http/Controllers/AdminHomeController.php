<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceModel;
use App\Models\CategoryModel;
use App\Models\AccessModel;
use App\Models\LatestDataModel;
use App\Models\DataModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminHomeController extends Controller
{
    public function index()
    {
        return view('admin.home');
    }


    public function manageHome()
    {
        //jika admin level master tampil semua
        if(Auth::user()->level == 'master'){
            $totalAll = DeviceModel::count();


        $categories = CategoryModel::select('category_name')
            ->withCount(['devices as total_items' => function($query) {
                // Count devices for each category
            }])
            ->get();

        // Row 2: Devices grouped by category for slider and percentage per category
        $devicesByCategory = CategoryModel::with(['devices' => function($query) {
            $query->orderBy('device_id');
        }])->get();

        $categoryPercentages = CategoryModel::select('category_name')
            ->withCount('devices')
            ->get()
            ->map(function($category) use ($totalAll) {
                return [
                    'category_name' => $category->category_name,
                    'count' => $category->devices_count,
                    'percentage' => $totalAll > 0 ? round(($category->devices_count / $totalAll) * 100, 2) : 0
                ];
            });

        // Row 3: All devices with details in card format
        $devices = DeviceModel::with(['category', 'sensors'])->get();


        } else {

            // selain itu hitung device yang usernya sama dengan user yang login
            $totalAll = DeviceModel::where('user_assigned', '=', Auth::user()->id)->count();
            $categories = CategoryModel::select('category_name')
                ->withCount(['devices as total_items' => function($query) {
                    $query->where('user_assigned', '=', Auth::user()->id);
                }])
                ->get();


            // Row 2: Devices grouped by category for slider and percentage per category
            $devicesByCategory = CategoryModel::with(['devices' => function($query) {
                $query->where('user_assigned', '=', Auth::user()->id)
                      ->orderBy('device_id');
            }])->get();
            $categoryPercentages = CategoryModel::select('category_name')
                ->withCount(['devices' => function($query) {
                    $query->where('user_assigned', '=', Auth::user()->id);
                }])
                ->get()
                ->map(function($category) use ($totalAll) {
                    return [
                        'category_name' => $category->category_name,
                        'count' => $category->devices_count,
                        'percentage' => $totalAll > 0 ? round(($category->devices_count / $totalAll) * 100, 2) : 0
                    ];
                });


            // Row 3: All devices with details in card format
            $devices = DeviceModel::with(['category', 'sensors'])
                ->where('user_assigned', '=', Auth::user()->id)
                ->get();

        }


        return view('admin.manage_home',[
            'totalAll' => $totalAll,
            'categories' => $categories,
            'devicesByCategory' => $devicesByCategory,
            'categoryPercentages' => $categoryPercentages,
            'devices' => $devices
        ]);
    }

    public function getAdminDevicesData()
    {
        $cacheKey = "admin:devices:home:" . Auth::user()->id;
        $cacheTTL = 2; // Cache 2 menit

        // Check if cached
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        if(Auth::user()->level == 'master'){
            // Jika master, tampilkan semua device categories dan devices dengan optimized queries
            $deviceCategories = CategoryModel::with(['devices' => function ($query) {
                $query->select('device_id', 'device_name', 'location', 'latitude', 'longitude', 'device_category',  'device_gap_timeout')
                      ->with('sensors:id,device_id,parameter_name')
                      ->orderBy('device_id', 'asc');
            }])
            ->select( 'category_name', 'category_icon')
            ->orderBy('category_name', 'asc')
            ->get();

        } else {
            // Selain master, tampilkan device categories dan devices yang usernya user assigned ke user yang login
            $deviceCategories = CategoryModel::with(['devices' => function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id)
                      ->select('device_id', 'device_name', 'location', 'latitude', 'longitude', 'device_category',  'device_gap_timeout', 'user_assigned')
                      ->with('sensors:id,device_id,parameter_name')
                      ->orderBy('device_id', 'asc');
            }])
            ->select( 'category_name', 'category_icon')
            ->whereHas('devices', function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id);
            })
            ->orderBy('category_name', 'asc')
            ->get();
        }


         // Batch-fetch device statuses (1 query instead of N)
        $deviceObjectsForStatus = $deviceCategories
            ->pluck('devices')
            ->flatten()
            ->map(fn($d) => ['device_id' => $d->device_id, 'device_gap_timeout' => $d->device_gap_timeout])
            ->values()
            ->toArray();
        $deviceStatuses = $this->getDeviceStatusBatch($deviceObjectsForStatus);

        // Build response dengan sensor data
        $result = $deviceCategories->map(function ($category) use ($deviceStatuses) {
            $category_obj = $category;

            // Skip jika kategori null
            if (!$category_obj || !$category_obj->devices) {
                return null;
            }

            $devices = $category_obj->devices->filter(function ($device) {
                return $device !== null;
            })->map(function ($device) use ($deviceStatuses) {


                return [
                    'device_id'   => $device->device_id,
                    'device_name' => $device->device_name,
                    'location'    => $device->location,
                    'latitude'    => $device->latitude,
                    'longitude'   => $device->longitude,
                    'status'      => $deviceStatuses[$device->device_id] ?? null,
                ];
            })->values();

            // Return null jika tidak ada device valid di kategori ini
            return $devices->isEmpty() ? null : [
                'device_category' => $category_obj->category_name,
                'category_icon'   => $category_obj->category_icon,
                'devices'         => $devices->toArray(),
            ];
        })->filter()->values()->toArray();

        // Cache hasil selama 2 menit
        Cache::put($cacheKey, $result, now()->addMinutes($cacheTTL));

        return response()->json($result);
    }


    private function getDeviceStatusBatch(array $devices): array
    {
        if (empty($devices)) {
            return [];
        }

        $deviceIds = array_column($devices, 'device_id');

        // One query: get max timestamp per device from tbl_latest_data
        $latestTimestamps = LatestDataModel::whereIn('device_id', $deviceIds)
            ->select('device_id', DB::raw('MAX(timestamp) as timestamp'))
            ->groupBy('device_id')
            ->pluck('timestamp', 'device_id');

        $now = Carbon::now()->timestamp;
        $statuses = [];

        foreach ($devices as $device) {
            $id  = is_array($device) ? $device['device_id'] : $device->device_id;
            $gap = is_array($device) ? ($device['device_gap_timeout'] ?? 3) : ($device->device_gap_timeout ?? 3);
            $ts  = $latestTimestamps[$id] ?? null;

            if ($ts === null) {
                $statuses[$id] = 'Offline';
            } else {
                $diffMinutes = ($now - $ts) / 60;
                $statuses[$id] = $diffMinutes > $gap ? 'Offline' : 'Online';
            }
        }

        return $statuses;
    }


    public function DeviceStatus($deviceId, $gapTimeout = 3)
    {
        // Cek status device berdasarkan latest data timestamp
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

    public function getLatestData($deviceId)
    {
        try {
            // Get latest data for each sensor of the device

            $device = DeviceModel::with('sensors')->where('device_id', $deviceId)
                ->firstOrFail();
            $sensorsData = [];
            foreach ($device->sensors as $sensor) {
                $latestData = LatestDataModel::where('device_id',$deviceId)
                    ->where('parameter_name', $sensor->parameter_name)
                        ->orderBy('recorded_at', 'desc')
                        ->first();

                 if ($latestData) {
                        $sensorsData[] = [
                            'parameter_name' => $sensor->parameter_name,
                            'parameter_label' => $sensor->parameter->parameter_label,
                            'latest_value' => $latestData->value,
                            'recorded_at' => $this->unixToDateTime($latestData->timestamp)->format('Y-m-d H:i:s'),
                        ];
                    }

            }

            return response()->json(['data' => $sensorsData]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data: ' . $e->getMessage()], 500);
        }
    }
}
