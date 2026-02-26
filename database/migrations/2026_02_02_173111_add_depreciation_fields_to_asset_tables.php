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
        $tables = [
            'golongan_peralatan_mesin',
            'golongan_bangunan_gedung',
            'golongan_jaringan_perpipaan',
            'golongan_aset_tetap_lainnya'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('nilai_buku', 15, 2)->nullable()->comment('Nilai buku aset saat ini setelah penyusutan');
                    $table->date('tanggal_penyusutan_terakhir')->nullable()->comment('Tanggal terakhir perhitungan penyusutan dilakukan');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'golongan_peralatan_mesin',
            'golongan_gedung_bangunan',
            'golongan_jaringan',
            'golongan_aset_tetap_lainnya'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['nilai_buku', 'tanggal_penyusutan_terakhir']);
                });
            }
        }
    }
};
