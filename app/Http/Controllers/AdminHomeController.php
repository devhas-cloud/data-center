<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceModel;
use App\Models\CategoryModel;
use App\Models\DataModel;
use Illuminate\Support\Facades\Auth;

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

    public function getLatestData($deviceId)
    {
        try {
            // Get latest data for each sensor of the device

            $device = DeviceModel::with('sensors')->where('device_id', $deviceId)
                ->firstOrFail();
            $sensorsData = [];
            foreach ($device->sensors as $sensor) {
                $latestData = DataModel::where('device_id',$deviceId)
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