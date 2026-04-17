<?php

namespace App\Observers;

use App\Models\DeviceModel;
use App\Models\AccessModel;
use App\Helpers\CacheHelper;

/**
 * Device Model Observer
 * 
 * Automatically invalidates caches when device data changes
 */
class DeviceObserver
{
    /**
     * Handle the DeviceModel "updated" event.
     */
    public function updated(DeviceModel $device): void
    {
        // Invalidate all device-related caches (info, maps, charts, reports)
        CacheHelper::invalidateAllDeviceCaches($device->device_id);
        
        // Also invalidate all users who have access to this device
        $accessRecords = AccessModel::where('device_id', $device->id)->get();
        foreach ($accessRecords as $access) {
            CacheHelper::invalidateUserDeviceCache($access->user_id);
        }
        
        \Log::info("All device caches invalidated: {$device->device_id}");
    }

    /**
     * Handle the DeviceModel "deleted" event.
     */
    public function deleted(DeviceModel $device): void
    {
        CacheHelper::invalidateAllDeviceCaches($device->device_id);
        \Log::info("Device deleted cache invalidated: {$device->device_id}");
    }
}
