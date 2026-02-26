<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('golongan_aset_tetap_lainnya', function (Blueprint $table) {
            $table->string('dokumentasi')->nullable()->change();
            $table->string('wilayah_id', 2)->nullable();
            $table->foreign('wilayah_id')->references('id')->on('struktur_wilayah')->onUpdate('cascade')->onDelete('set null');
            $table->string('sub_bidang_id', 2)->nullable();
            $table->foreign('sub_bidang_id')->references('id')->on('struktur_sub_bidang')->onUpdate('cascade')->onDelete('set null');
            $table->string('unit_id', 2)->nullable();
            $table->foreign('unit_id')->references('id')->on('struktur_unit')->onUpdate('cascade')->onDelete('set null');
            $table->string('sub_sub_kelompok_id', 23)->nullable();
            $table->foreign('sub_sub_kelompok_id')->references('id')->on('asset_sub_sub_kelompok')->onUpdate('cascade')->onDelete('set null');
            $table->year('tahun')->nullable();
            $table->string('kode_lokasi', 17)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
