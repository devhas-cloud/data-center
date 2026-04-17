<?php

namespace App\Observers;

use App\Models\SensorModel;
use App\Helpers\CacheHelper;

/**
 * Sensor Model Observer
 * 
 * Automatically invalidates sensor configuration caches
 */
class SensorObserver
{
    /**
     * Handle the SensorModel "updated" event.
     */
    public function updated(SensorModel $sensor): void
    {
        // Invalidate sensor configuration cache
        CacheHelper::invalidateSensorCache($sensor->device_id, $sensor->parameter_name);
        
        \Log::info("Sensor cache invalidated: {$sensor->device_id} - {$sensor->parameter_name}");
    }

    /**
     * Handle the SensorModel "created" event.
     */
    public function created(SensorModel $sensor): void
    {
        // Invalidate device cache since sensor configuration changed
        CacheHelper::invalidateDeviceCache($sensor->device_id);
        
        \Log::info("Device cache invalidated (new sensor): {$sensor->device_id}");
    }

    /**
     * Handle the SensorModel "deleted" event.
     */
    public function deleted(SensorModel $sensor): void
    {
        CacheHelper::invalidateDeviceCache($sensor->device_id);
        
        \Log::info("Device cache invalidated (sensor deleted): {$sensor->device_id}");
    }
}
