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
        Schema::create('tbl_syslog_header', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index();
            $table->string('user_assigned')->index();
            $table->date('created_date');
            $table->enum('category', ['maintenance','calibration','installation']);
            $table->string('note')->nullable();
            $table->string('linked_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_syslog_header');
    }
};
