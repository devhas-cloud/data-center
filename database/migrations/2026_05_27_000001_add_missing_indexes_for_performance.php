<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // tbl_access: no indexes at all — queried on every request
        Schema::table('tbl_access', function (Blueprint $table) {
            // Individual indexes for single-column lookups
            if (!$this->indexExists('tbl_access', 'idx_access_user_id')) {
                $table->index('user_id', 'idx_access_user_id');
            }
            if (!$this->indexExists('tbl_access', 'idx_access_device_id')) {
                $table->index('device_id', 'idx_access_device_id');
            }
            // Composite for the most common query: WHERE user_id = ? (+ join/pluck device_id)
            if (!$this->indexExists('tbl_access', 'idx_access_user_device')) {
                $table->index(['user_id', 'device_id'], 'idx_access_user_device');
            }
        });

        // tbl_data: add composite on (device_id, parameter_name, timestamp)
        // All time-range queries filter by timestamp (integer unix), not recorded_at
        Schema::table('tbl_data', function (Blueprint $table) {
            if (!$this->indexExists('tbl_data', 'idx_data_device_param_ts')) {
                $table->index(['device_id', 'parameter_name', 'timestamp'], 'idx_data_device_param_ts');
            }
            // For DeviceStatus / per-device latest-timestamp queries
            if (!$this->indexExists('tbl_data', 'idx_data_device_timestamp')) {
                $table->index(['device_id', 'timestamp'], 'idx_data_device_timestamp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_access', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_access_user_id');
            $table->dropIndexIfExists('idx_access_device_id');
            $table->dropIndexIfExists('idx_access_user_device');
        });

        Schema::table('tbl_data', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_data_device_param_ts');
            $table->dropIndexIfExists('idx_data_device_timestamp');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
