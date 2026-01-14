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
        Schema::create('tbl_auto_report', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index();
            $table->string('schedule_report')->nullable();
            $table->string('email_report')->nullable();
            $table->string('auto_report')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_auto_report');
    }
};
