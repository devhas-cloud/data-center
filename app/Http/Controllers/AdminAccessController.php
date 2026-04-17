<?php

namespace App\Http\Controllers;

use App\Models\AccessModel;
use App\Models\User;
use App\Models\CategoryModel;
use App\Models\DeviceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class AdminAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all users (only role 'user')
        // jika user login level master maka tampilkan semua user dengan role user
        if(Auth::user()->level == 'master'){

            $users = User::where('role','!=','admin')->orderBy('name', 'asc')->get();
        } else {
            // selain itu tampilkan user dengan role user yang dibuat oleh user yang login
            $users = User::where('access', Auth::user()->username)->orderBy('name', 'asc')->get();
        }
        
        // Get all categories with their devices
        if(Auth::user()->level == 'master'){
            $categories = CategoryModel::with('devices')->orderBy('category_name', 'asc')->get();
        } else {
            $categories = CategoryModel::with(['devices' => function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id);  
            }])
            ->whereHas('devices', function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id);
            })
            ->orderBy('category_name', 'asc')
            ->get();
        }
        
        
        return view('admin.manage_access', compact('users', 'categories'));
    }

    /**
     * Get user's current access
     */
    public function getUserAccess($userId)
    {
        $access = AccessModel::where('user_id', $userId)
            ->select('id', 'user_id', 'device_id', 'category_id')
            ->get();
        
        return response()->json([
            'success' => true,
            'access' => $access
        ]);
    }

    /**
     * Update user's access
     */
    public function updateUserAccess(Request $request, $userId)
    {
        try {
            $validated = $request->validate([
                'devices' => 'array',
                'devices.*.device_id' => 'required|integer|exists:tbl_device,id',
                'devices.*.category_id' => 'required|integer|exists:tbl_category,id'
            ]);

            DB::beginTransaction();

            // Delete all existing access for this user
            AccessModel::where('user_id', $userId)->delete();

            // Insert new access
            $accessData = [];
            $devices = $request->input('devices', []);
            
            foreach ($devices as $device) {
                $accessData[] = [
                    'user_id' => $userId,
                    'device_id' => $device['device_id'],
                    'category_id' => $device['category_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($accessData)) {
                AccessModel::insert($accessData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Access updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update access: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AccessModel $accessModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccessModel $accessModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccessModel $accessModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessModel $accessModel)
    {
        //
    }
}
