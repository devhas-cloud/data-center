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
            $table->date('date_installation')->nullable()->after('device_gap_timeout');
            $table->string('linked_img')->nullable()->after('date_installation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_device', function (Blueprint $table) {
            $table->dropColumn(['date_installation', 'linked_img']);
        });
    }
};
