<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\DeviceModel;
use App\Models\SensorModel;
use App\Models\AccessModel;
use App\Models\DataModel;
use App\Observers\DeviceObserver;
use App\Observers\SensorObserver;
use App\Observers\AccessObserver;
use App\Observers\DataObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');

            if (request()->header('x-forwarded-proto') === 'https') {
                request()->server->set('HTTPS', 'on');
            }
        }

        // ============================================
        // OBSERVERS FOR AUTOMATIC CACHE INVALIDATION
        // ============================================
        // Register model observers to automatically invalidate
        // Redis caches when data changes in database
        
        //DeviceModel::observe(DeviceObserver::class);
        //SensorModel::observe(SensorObserver::class);
        //AccessModel::observe(AccessObserver::class);
        //DataModel::observe(DataObserver::class);
        
        //\Log::info('✓ Cache observers registered and active');
    }
}
