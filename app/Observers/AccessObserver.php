<?php

namespace App\Observers;

use App\Models\AccessModel;
use App\Helpers\CacheHelper;

/**
 * Access Model Observer
 * 
 * Automatically invalidates user device caches when permissions change
 */
class AccessObserver
{
    /**
     * Handle the AccessModel "created" event.
     */
    public function created(AccessModel $access): void
    {
        // Invalidate user's device list cache
        CacheHelper::invalidateUserDeviceCache($access->user_id);
        
        \Log::info("User device cache invalidated: User {$access->user_id}");
    }

    /**
     * Handle the AccessModel "updated" event.
     */
    public function updated(AccessModel $access): void
    {
        CacheHelper::invalidateUserDeviceCache($access->user_id);
        
        \Log::info("User device cache invalidated (access updated): User {$access->user_id}");
    }

    /**
     * Handle the AccessModel "deleted" event.
     */
    public function deleted(AccessModel $access): void
    {
        CacheHelper::invalidateUserDeviceCache($access->user_id);
        
        \Log::info("User device cache invalidated (access deleted): User {$access->user_id}");
    }
}
