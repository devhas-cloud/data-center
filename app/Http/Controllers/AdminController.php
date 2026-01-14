<?php

namespace App\Http\Controllers;

use App\Models\DeviceModel;
use App\Models\LogsModel;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    
    // logs belum dibaca user
    public function getUnreadLogsCount()
    {
        $unreadCount = LogsModel::with('devices')
            ->whereHas('devices', function ($query) {
                if(Auth::user()->level != 'master')
                    $query->where('user_assigned', Auth::user()->id);
            })
            ->where('is_read_admin', false)
            ->get();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount->count(),
        ], 200);
    }



    // Mark logs as read when modal closes
    public function markLogsAsRead()
    {
        try {
            // Get all device IDs accessible by user
            $deviceIds = DeviceModel::where('user_assigned', Auth::user()->id)
                ->get()
                ->pluck('device_id')
                ->filter(); // Remove null device_ids

            // Update all unread logs for these devices
            LogsModel::whereIn('device_id', $deviceIds)
                ->where('is_read_admin', false)
                ->update(['is_read_admin' => true]);

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
