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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->unsignedSmallInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            
            // Custom morph for string IDs
            $table->string('maintenanceable_type');
            $table->string('maintenanceable_id')->nullable();
            $table->index(['maintenanceable_type', 'maintenanceable_id'], 'mt_req_morph_idx');

            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('prioritas', ['Rendah', 'Sedang', 'Tinggi', 'Darurat'])->default('Sedang');
            $table->enum('status', ['Pending', 'Disetujui', 'Ditolak', 'Selesai'])->default('Pending');
            $table->date('tanggal_laporan')->default(now());
            $table->string('bukti_foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
