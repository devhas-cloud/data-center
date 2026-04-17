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
        Schema::create('tbl_latest_data', function (Blueprint $table) {
            // ID tidak perlu auto increment karena primary key kita adalah kombinasi device_id + parameter_name
            $table->bigIncrements('id');

            $table->timestamp('recorded_at')->nullable()->index();
            $table->bigInteger('timestamp')->nullable()->index();
            $table->string('device_id', 255)->index();
            $table->string('parameter_name', 255)->index();
            $table->decimal('value', 10, 2)->nullable();

            $table->dateTime('created_at', 3)->nullable();
            $table->dateTime('updated_at', 3)->nullable();

            // Primary key kombinasi device_id + parameter_name untuk upsert
            $table->unique(['device_id', 'parameter_name'], 'latest_data_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('latest_data');
    }
};