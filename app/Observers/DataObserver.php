<?php

namespace App\Observers;

use App\Models\DataModel;
use App\Helpers\CacheHelper;

/**
 * Data Model Observer
 * 
 * Automatically invalidates chart and report caches when data changes
 * This is critical because charts and reports depend on tbl_data values
 */
class DataObserver
{
    /**
     * Handle the DataModel "created" event.
     * 
     * When new sensor data is recorded, invalidate chart caches
     */
    public function created(DataModel $data): void
    {
        // Invalidate chart caches for this device
        // New data means chart needs to be refreshed
        CacheHelper::invalidateDeviceCharts($data->device_id);
        
        // Also invalidate historical chart cache
        CacheHelper::invalidateHistoricalChart($data->device_id);
        
        // Log the cache invalidation
        \Log::debug("Chart cache invalidated (new data): Device {$data->device_id}");
    }
}
