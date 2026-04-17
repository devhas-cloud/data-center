<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\DeviceModel;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminDeviceController extends Controller
{
    public function index()
    {
        if(Auth::user()->level === 'master'){
            $devices = DeviceModel::with('user')->get();
            
        } else {
            $devices = DeviceModel::with('user')->where('user_assigned', '=', Auth::user()->id)->get();
        }
        return response()->json($devices);
        
    }

    public function manageDevices()
    {
        $categories = CategoryModel::all();
        $users = User::where('role', '=', 'admin')->get();
        return view('admin.manage_devices', ['categories' => $categories, 'users' => $users]);
    }

    public function show($id)
    {
        // Validate ID is numeric to prevent SQL injection
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['message' => 'Invalid device ID'], 400);
        }

        // Optional: Add authorization check
        // $this->authorize('view', DeviceModel::class);

        $device = DeviceModel::find($id);
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }
        return response()->json($device);
    }

    public function store(Request $request)
    {
        // Optional: Add authorization check
        // $this->authorize('create', DeviceModel::class);

        try {
            $data = $request->validate(
                [
                    'device_id'       => [
                        'required',
                        'string',
                        'max:255',
                        'regex:/^[a-zA-Z0-9_-]+$/', // Only alphanumeric, dash, underscore
                        'unique:tbl_device,device_id'
                    ],
                    'device_category' => 'required|string|max:255',
                    'device_name'   => 'nullable|string|max:255',
                    'device_ip'      => 'nullable|ip',
                    'device_gap_timeout' => 'nullable|integer|min:1',
                    'location'        => 'nullable|string|max:255',
                    'district'        => 'nullable|string|max:255',
                    'latitude'        => 'nullable|numeric|between:-90,90',
                    'longitude'       => 'nullable|numeric|between:-180,180',
                    'user_assigned'   => 'sometimes|nullable',
                    'date_installation' => 'nullable|date',
                    'linked_img'      => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                ],
                [
                    'device_id.required' => 'Device ID is required',
                    'device_id.regex' => 'Device ID can only contain letters, numbers, dash, and underscore',
                    'device_id.unique' => 'Device ID already exists',
                    'device_ip.ip' => 'Device IP must be a valid IP address',
                    'device_gap_timeout.integer' => 'Device GAP Timeout must be an integer',
                    'device_gap_timeout.min' => 'Device GAP Timeout must be at least 1',
                    'device_category.required' => 'Device category is required',
                    'latitude.between' => 'Latitude must be between -90 and 90',
                    'longitude.between' => 'Longitude must be between -180 and 180',

                ]
            );

            //user assigned default, id user yang buat device
            if (empty($data['user_assigned'])) {
                $data['user_assigned'] = Auth::id();
            } 


            // Sanitize string inputs
            $data['device_id'] = trim($data['device_id']);
            $data['device_category'] = trim($data['device_category']);
            if (isset($data['device_name'])) $data['device_name'] = trim($data['device_name']);
            if (isset($data['device_ip'])) $data['device_ip'] = trim($data['device_ip']);
            if (isset($data['device_gap_timeout'])) $data['device_gap_timeout'] = trim($data['device_gap_timeout']);
            if (isset($data['location'])) $data['location'] = trim($data['location']);
            if (isset($data['district'])) $data['district'] = trim($data['district']);

            // Handle image upload
            if ($request->hasFile('linked_img')) {
                $file = $request->file('linked_img');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('device_images', $filename, 'public');
                $data['linked_img'] = $path;
            }

            $device = DeviceModel::create($data);

            return response()->json([
                'message' => 'Device created successfully',
                'data'    => $device
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose details to client
            Log::error('Device creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create device. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate ID is numeric to prevent SQL injection
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['message' => 'Invalid device ID'], 400);
        }

        try {
            $device = DeviceModel::find($id);
            if (!$device) {
                return response()->json(['message' => 'Device not found'], 404);
            }

            // Optional: Add authorization check
            // $this->authorize('update', $device);

            $data = $request->validate(
                [
                    'device_id'       => [
                        'sometimes',
                        'required',
                        'string',
                        'max:255',
                        'regex:/^[a-zA-Z0-9_-]+$/',
                        Rule::unique('tbl_device', 'device_id')->ignore($device->id)
                    ],
                    'device_category' => 'sometimes|required|string|max:255',
                    'device_name'   => 'sometimes|nullable|string|max:255',
                    'device_ip'      => 'sometimes|nullable|ip',
                    'device_gap_timeout' => 'sometimes|nullable|integer|min:1',
                    'location'        => 'sometimes|nullable|string|max:255',
                    'district'        => 'sometimes|nullable|string|max:255',
                    'latitude'        => 'sometimes|nullable|numeric|between:-90,90',
                    'longitude'       => 'sometimes|nullable|numeric|between:-180,180',
                    'user_assigned'   => 'sometimes|nullable',
                    'date_installation' => 'sometimes|nullable|date',
                    'linked_img'      => 'sometimes|nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                ],
                [
                    'device_id.regex' => 'Device ID can only contain letters, numbers, dash, and underscore',
                    'device_id.unique' => 'Device ID already exists',
                    'device_ip.ip' => 'Device IP must be a valid IP address',
                    'device_gap_timeout.integer' => 'Device GAP Timeout must be an integer',
                    'device_gap_timeout.min' => 'Device GAP Timeout must be at least 1',
                    'device_category.required' => 'Device category is required',
                    'latitude.between' => 'Latitude must be between -90 and 90',
                    'longitude.between' => 'Longitude must be between -180 and 180',
                ]
            );

            //jika user assigned kosong, set update id user yang update
            if (empty($data['user_assigned'])) {
                $data['user_assigned'] = Auth::id();
            }

            



            // Sanitize string inputs
            if (isset($data['device_id'])) $data['device_id'] = trim($data['device_id']);
            if (isset($data['device_category'])) $data['device_category'] = trim($data['device_category']);
            if (isset($data['device_name'])) $data['device_name'] = trim($data['device_name']);
            if (isset($data['device_ip'])) $data['device_ip'] = trim($data['device_ip']);
            if (isset($data['device_gap_timeout'])) $data['device_gap_timeout'] = trim($data['device_gap_timeout']);
            if (isset($data['location'])) $data['location'] = trim($data['location']);
            if (isset($data['district'])) $data['district'] = trim($data['district']);

            // Handle image upload
            if ($request->hasFile('linked_img')) {
                // Delete old image if exists
                if ($device->linked_img && Storage::disk('public')->exists($device->linked_img)) {
                    Storage::disk('public')->delete($device->linked_img);
                }
                
                $file = $request->file('linked_img');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('device_images', $filename, 'public');
                $data['linked_img'] = $path;
            }

            $device->update($data);

            return response()->json([
                'message' => 'Device updated successfully',
                'data'    => $device
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose details to client
            Log::error('Device update failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update device. Please try again.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        // Validate ID is numeric to prevent SQL injection
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['message' => 'Invalid device ID'], 400);
        }

        try {
            $device = DeviceModel::find($id);
            if (!$device) {
                return response()->json(['message' => 'Device not found'], 404);
            }

            // Optional: Add authorization check
            // $this->authorize('delete', $device);

            // Optional: Check if device has related data before deleting
            // if ($device->sensors()->count() > 0) {
            //     return response()->json([
            //         'message' => 'Cannot delete device with existing sensors'
            //     ], 422);
            // }

            // Delete image if exists
            if ($device->linked_img && Storage::disk('public')->exists($device->linked_img)) {
                Storage::disk('public')->delete($device->linked_img);
            }

            $device->delete();

            return response()->json([
                'message' => 'Device deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose details to client
            Log::error('Device deletion failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete device. Please try again.',
            ], 500);
        }
    }

   



}
