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
        // Menambahakan sensor_number ( serial number pada sensor )
        // Menambahkan parameter_number ( serial number pada parameter sensor )
        // Menambahkan Status ( aktif / non aktif ) pada parameter sensor
        // menambahakan kolom catatan pada tabel tbl_sensor
        Schema::table('tbl_sensor', function (Blueprint $table) {

            $table->string('sensor_number', 100)->after('device_id')->nullable();
            $table->string('parameter_number', 100)->after('sensor_name')->nullable();
            $table->string('status', 50)->default('active')->after('calibration_date');
            $table->text('notes')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_sensor', function (Blueprint $table) {
            $table->dropColumn('sensor_number');
            $table->dropColumn('parameter_number');
            $table->dropColumn('status');
            $table->dropColumn('notes');
        });
    }
};
