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
        Schema::table('tbl_logs', function (Blueprint $table) {
            $table->boolean('is_read_admin')->default(false)->after('action');
            $table->boolean('is_read_user')->default(false)->after('is_read_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_logs', function (Blueprint $table) {
            $table->dropColumn('is_read_admin');
            $table->dropColumn('is_read_user');

        });
    }
};
