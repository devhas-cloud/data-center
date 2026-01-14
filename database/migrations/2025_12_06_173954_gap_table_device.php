<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_device', function (Blueprint $table) {
            $table->integer('device_gap_timeout')->nullable()->after('device_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_device', function (Blueprint $table) {
            $table->dropColumn('device_gap_timeout');
        });
    }
};
