<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

/**
 * Cache Management Helper
 * 
 * Provides consistent methods for cache invalidation across the application
 * Based on Redis caching strategy implemented in UserController
 */
class CacheHelper
{
    /**
     * Invalidate all caches related to a device
     * 
     * Called when device information is updated
     */
    public static function invalidateDeviceCache($deviceId)
    {
        Cache::forget("device:{$deviceId}:info");
        Cache::forget("device:{$deviceId}:maps");
        Cache::forget("device:{$deviceId}:latest");
        Cache::forget("device:{$deviceId}:progress");
        Cache::forget("device:{$deviceId}:windrose");
        
        // Invalidate all chart caches for this device
        self::invalidateDeviceCharts($deviceId);
    }

    /**
     * Invalidate all chart caches for a device
     */
    public static function invalidateDeviceCharts($deviceId)
    {
        // Get all parameters and invalidate their charts
        // This is a wildcard approach - Redis supports pattern matching
        $parameters = ["temperature", "humidity", "pressure", "wspeed", "wdir"]; // Common params
        
        foreach ($parameters as $param) {
            Cache::forget("device:{$deviceId}:chart:line:{$param}");
            Cache::forget("device:{$deviceId}:chart:bar:{$param}");
        }
    }

    /**
     * Invalidate sensor configuration cache
     * 
     * Called when sensor settings are updated
     */
    public static function invalidateSensorCache($deviceId, $parameterName = null)
    {
        if ($parameterName) {
            // Invalidate specific sensor
            Cache::forget("device:{$deviceId}:sensor:{$parameterName}");
            // Also invalidate related charts
            Cache::forget("device:{$deviceId}:chart:line:{$parameterName}");
            Cache::forget("device:{$deviceId}:chart:bar:{$parameterName}");
        } else {
            // Invalidate all sensors for device
            $pattern = "device:{$deviceId}:sensor:*";
            self::forgetByPattern($pattern);
        }
    }

    /**
     * Invalidate user's device list cache
     * 
     * Called when user access permissions change
     */
    public static function invalidateUserDeviceCache($userId)
    {
        Cache::forget("user:{$userId}:devices:home");
        Cache::forget("user:{$userId}:devices:dashboard");
    }

    /**
     * Invalidate historical chart cache
     * 
     * Note: Historical data caches include date range, so specific invalidation may be needed
     */
    public static function invalidateHistoricalChart($deviceId, $parameterName = null, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            // Invalidate specific date range
            $cacheKey = "device:{$deviceId}:chart:historical:{$parameterName}:{$startDate}:{$endDate}";
            Cache::forget($cacheKey);
        } else if ($parameterName) {
            // Invalidate all historical data for parameter (wildcard approach needed)
            // This is tricky - might need to store keys for bulk invalidation
            self::forgetByPattern("device:{$deviceId}:chart:historical:{$parameterName}:*");
        } else {
            // Invalidate all historical data for device
            self::forgetByPattern("device:{$deviceId}:chart:historical:*");
        }
    }

    /**
     * Clear all caches related to a user
     */
    public static function invalidateUserCaches($userId)
    {
        self::invalidateUserDeviceCache($userId);
        Cache::forget("user:{$userId}:*");
    }

    /**
     * Clear all application caches (use with caution!)
     */
    public static function clearAllCaches()
    {
        Cache::flush();
    }

    /**
     * Invalidate caches by pattern (requires Redis)
     * 
     * Note: This uses Redis KEYS command which is not recommended for large datasets
     * For production, consider maintaining a registry of cache keys
     */
    protected static function forgetByPattern($pattern)
    {
        try {
            $redis = Cache::getStore()->connection();
            
            // Convert wildcard pattern to Redis pattern
            $keys = $redis->keys($pattern);
            
            if (!empty($keys)) {
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Cache pattern invalidation failed: " . $e->getMessage());
        }
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats()
    {
        try {
            $redis = Cache::getStore()->connection();
            $info = $redis->info('stats');
            
            return [
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'keys_count' => $redis->dbsize(),
                'memory_mb' => ($info['used_memory'] ?? 0) / (1024 * 1024),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Invalidate all report caches for a device
     * 
     * Clears all export and table report caches
     * Called when device data is updated
     */
    public static function invalidateReportCache($deviceId)
    {
        try {
            // Use pattern matching to clear all report caches for this device
            self::forgetByPattern("device:{$deviceId}:report:*");
            self::forgetByPattern("device:{$deviceId}:table-report:*");
        } catch (\Exception $e) {
            \Log::warning("Report cache invalidation failed: " . $e->getMessage());
        }
    }

    /**
     * Invalidate all caches: device, charts, and reports (nuclear option)
     */
    public static function invalidateAllDeviceCaches($deviceId)
    {
        self::invalidateDeviceCache($deviceId);
        self::invalidateReportCache($deviceId);
    }

    /**
     * Manual cache warming for frequently accessed data
     * Call this during off-peak hours
     */
    public static function warmDeviceCache($deviceId, $ttl = 5)
    {
        // This would load device info into cache proactively
        // Implementation depends on your specific needs
    }
}
