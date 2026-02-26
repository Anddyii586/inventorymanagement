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
        // Fix for missing columns in golongan_bangunan_gedung due to typo in previous migration
        if (Schema::hasTable('golongan_bangunan_gedung')) {
            Schema::table('golongan_bangunan_gedung', function (Blueprint $table) {
                if (!Schema::hasColumn('golongan_bangunan_gedung', 'nilai_buku')) {
                    $table->decimal('nilai_buku', 15, 2)->nullable()->comment('Nilai buku aset saat ini setelah penyusutan');
                }
                if (!Schema::hasColumn('golongan_bangunan_gedung', 'tanggal_penyusutan_terakhir')) {
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
        if (Schema::hasTable('golongan_bangunan_gedung')) {
            Schema::table('golongan_bangunan_gedung', function (Blueprint $table) {
                // Only drop if they exist to avoid errors in down
                $columnsToDrop = [];
                if (Schema::hasColumn('golongan_bangunan_gedung', 'nilai_buku')) {
                    $columnsToDrop[] = 'nilai_buku';
                }
                if (Schema::hasColumn('golongan_bangunan_gedung', 'tanggal_penyusutan_terakhir')) {
                    $columnsToDrop[] = 'tanggal_penyusutan_terakhir';
                }
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
