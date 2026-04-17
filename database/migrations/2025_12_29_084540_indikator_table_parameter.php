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
        
        Schema::table('tbl_parameter', function (Blueprint $table) {
            $table->integer('parameter_indicator_min')->default(0)->after('parameter_label');
            $table->integer('parameter_indicator_max')->default(0)->after('parameter_indicator_min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_parameter', function (Blueprint $table) {
            $table->dropColumn('parameter_indicator_min');
            $table->dropColumn('parameter_indicator_max');
        });
    }
};
