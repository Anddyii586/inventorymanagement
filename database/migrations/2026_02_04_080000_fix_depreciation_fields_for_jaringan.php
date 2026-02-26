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
        // Fix for missing columns in golongan_jaringan_perpipaan due to typo in previous migration
        if (Schema::hasTable('golongan_jaringan_perpipaan')) {
            Schema::table('golongan_jaringan_perpipaan', function (Blueprint $table) {
                if (!Schema::hasColumn('golongan_jaringan_perpipaan', 'nilai_buku')) {
                    $table->decimal('nilai_buku', 15, 2)->nullable()->comment('Nilai buku aset saat ini setelah penyusutan');
                }
                if (!Schema::hasColumn('golongan_jaringan_perpipaan', 'tanggal_penyusutan_terakhir')) {
                    $table->date('tanggal_penyusutan_terakhir')->nullable()->comment('Tanggal terakhir perhitungan penyusutan dilakukan');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('golongan_jaringan_perpipaan')) {
            Schema::table('golongan_jaringan_perpipaan', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('golongan_jaringan_perpipaan', 'nilai_buku')) {
                    $columnsToDrop[] = 'nilai_buku';
                }
                if (Schema::hasColumn('golongan_jaringan_perpipaan', 'tanggal_penyusutan_terakhir')) {
                    $columnsToDrop[] = 'tanggal_penyusutan_terakhir';
                }
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
