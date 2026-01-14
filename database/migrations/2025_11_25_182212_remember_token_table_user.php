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
       //add remember_token to tbl_user
       Schema::table('tbl_user', function (Blueprint $table) {
           $table->string('remember_token', 100)->nullable()->after('api_key');
         });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_user', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
