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
        Schema::table('tbl_sensor', function (Blueprint $table) {
            $table->integer('parameter_indicator_min')->after('parameter_name')->nullable();
            $table->integer('parameter_indicator_max')->after('parameter_indicator_min')->nullable();
            $table->integer('parameter_indicator_alert')->after('parameter_indicator_range')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_sensor', function (Blueprint $table) {
            $table->dropColumn('parameter_indicator_min');
            $table->dropColumn('parameter_indicator_max');
            $table->dropColumn('parameter_indicator_alert');
        });
    }
};
