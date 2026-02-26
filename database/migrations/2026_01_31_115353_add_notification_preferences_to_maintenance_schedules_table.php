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
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->boolean('enable_notifikasi')->default(true)->after('is_aktif');
            $table->integer('notifikasi_hari_sebelumnya')->default(3)->after('enable_notifikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropColumn(['enable_notifikasi', 'notifikasi_hari_sebelumnya']);
        });
    }
};
