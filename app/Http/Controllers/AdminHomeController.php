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
        $cacheTTL = 5; // Cache 5 menit

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

        // Collect device IDs untuk fetch latest data
        $deviceIds = $deviceCategories
            ->pluck('devices')
            ->flatten()
            ->pluck('device_id')
            ->filter()
            ->unique()
            ->toArray();

        // Early exit jika tidak ada devices
        if (empty($deviceIds)) {
            Cache::put($cacheKey, [], now()->addMinutes($cacheTTL));
            return response()->json([]);
        }

        // Fetch latest data untuk semua devices sekaligus (efficient)
        $latestDataCollection = LatestDataModel::whereIn('device_id', $deviceIds)
            ->get()
            ->keyBy(function ($item) {
                return $item->device_id . '|' . $item->parameter_name;
            });

        // Build response dengan sensor data
        $result = $deviceCategories->map(function ($category) use ($latestDataCollection) {
            $category_obj = $category;
            
            // Skip jika kategori null
            if (!$category_obj || !$category_obj->devices) {
                return null;
            }

            $devices = $category_obj->devices->filter(function ($device) {
                return $device !== null;
            })->map(function ($device) use ($latestDataCollection) {
                $sensorsData = $device->sensors->map(function ($sensor) use ($device, $latestDataCollection) {
                    $key = $device->device_id . '|' . $sensor->parameter_name;
                    $latestData = $latestDataCollection->get($key);

                    // Jika tidak ada latest data, return null
                    if (!$latestData) {
                        return null;
                    }

                    return [
                        'parameter_name' => $sensor->parameter_name,
                        'latest_value'   => $latestData->value,
                        'recorded_at'    => $this->unixToDateTime($latestData->timestamp)->format('Y-m-d H:i:s'),
                    ];
                })->filter()->values();

                return [
                    'device_id'   => $device->device_id,
                    'device_name' => $device->device_name,
                    'location'    => $device->location,
                    'latitude'    => $device->latitude,
                    'longitude'   => $device->longitude,
                    'status'      => $this->DeviceStatus($device->device_id, $device->device_gap_timeout),
                    'sensors'     => $sensorsData->toArray(),
                ];
            })->values();

            // Return null jika tidak ada device valid di kategori ini
            return $devices->isEmpty() ? null : [
                'device_category' => $category_obj->category_name,
                'category_icon'   => $category_obj->category_icon,
                'devices'         => $devices->toArray(),
            ];
        })->filter()->values()->toArray();

        // Cache hasil selama 5 menit
        Cache::put($cacheKey, $result, now()->addMinutes($cacheTTL));

        return response()->json($result);
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
                            'recorded_at' => date("Y-m-d H:i", strtotime($latestData->recorded_at)),
                        ];
                    }
               
            }
            
            return response()->json(['data' => $sensorsData]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data: ' . $e->getMessage()], 500);
        }
    }
}