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
        // Adding 'level' column to 'tbl_user' table
        Schema::table('tbl_user', function (Blueprint $table) {
            $table->string('level')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        // Dropping 'level' column from 'tbl_user' table
        Schema::table('tbl_user', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
