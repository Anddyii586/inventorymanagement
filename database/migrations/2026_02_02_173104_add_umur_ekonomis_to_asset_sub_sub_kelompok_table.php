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
        Schema::table('asset_sub_sub_kelompok', function (Blueprint $table) {
            $table->integer('umur_ekonomis')->nullable()->after('sub_sub_kelompok')->comment('Umur ekonomis dalam tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_sub_sub_kelompok', function (Blueprint $table) {
            $table->dropColumn('umur_ekonomis');
        });
    }
};
