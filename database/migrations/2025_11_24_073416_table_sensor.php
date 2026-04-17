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
        Schema::create('tbl_sensor', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('sensor_name');
            $table->string('parameter_name');
            $table->string('sensor_unit');
            $table->date('calibration_date')->nullable();
            $table->date('maintenance_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_sensor');
    }
};
