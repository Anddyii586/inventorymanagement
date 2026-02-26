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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            // Custom morph columns to support varchar IDs
            $table->string('maintenanceable_type');
            $table->string('maintenanceable_id');
            $table->index(['maintenanceable_type', 'maintenanceable_id']);
            
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('jenis_pemeliharaan'); // Perbaikan, Rutin, Inspeksi
            $table->text('deskripsi')->nullable();
            $table->decimal('biaya', 15, 2)->default(0);
            $table->string('pelaksana')->nullable(); // Nama Mekanik / Vendor
            $table->string('status')->default('Selesai'); // Selesai, Dalam Proses, Dibatalkan
            
            // Adjust user_id to match users table (smallint)
            $table->unsignedSmallInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->timestamps();
        });

        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            // Custom morph columns
            $table->string('maintenanceable_type');
            $table->string('maintenanceable_id');
            $table->index(['maintenanceable_type', 'maintenanceable_id'], 'm_schedules_morph_index');
            
            $table->string('nama_tugas');
            $table->string('frekuensi'); // Harian, Mingguan, Bulanan, Tahunan
            $table->date('tanggal_terakhir')->nullable();
            $table->date('tanggal_berikutnya');
            $table->boolean('is_aktif')->default(true);
            
            // Adjust user_id to match users table (smallint)
            $table->unsignedSmallInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('maintenance_logs');
    }
};
