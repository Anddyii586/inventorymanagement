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
        Schema::table('golongan_peralatan_mesin', function (Blueprint $table) {
            // Kategori KIB B
            $table->enum('kategori', ['Peralatan', 'Kendaraan Dinas', 'Pompa'])->default('Peralatan')->after('id');
            
            // Kolom nama barang (yang diminta user)
            $table->string('nama_barang')->nullable()->after('kategori');
            
            // Kolom tahun pembelian (yang diminta user)
            $table->year('tahun_pembelian')->nullable()->after('tanggal_pengadaan');
            
            // Kolom khusus Kendaraan Dinas
            $table->string('nomor_pabrik')->nullable()->after('tahun_pembelian');
            $table->string('nomor_rangka')->nullable()->after('nomor_pabrik');
            $table->string('nomor_mesin')->nullable()->after('nomor_rangka');
            $table->string('nomor_polisi')->nullable()->after('nomor_mesin');
            $table->string('bpkb')->nullable()->after('nomor_polisi');
            
            // Kolom khusus Pompa
            $table->string('kapasitas_listrik_kwh')->nullable()->after('bpkb');
            $table->string('kapasitas_air')->nullable()->after('kapasitas_listrik_kwh');
            $table->string('head_tekanan')->nullable()->after('kapasitas_air');
            $table->string('merk_panel_pompa')->nullable()->after('head_tekanan');
            $table->string('tipe_panel_pompa')->nullable()->after('merk_panel_pompa');
            $table->boolean('rtu')->nullable()->after('tipe_panel_pompa');
            
            // Kolom kelistrikan (untuk peralatan dan pompa)
            $table->string('kapasitas_listrik_va')->nullable()->after('rtu');
            $table->string('slo')->nullable()->after('kapasitas_listrik_va');
            $table->string('jil')->nullable()->after('slo');
            $table->text('genset')->nullable()->after('jil');
            $table->boolean('panel_listrik')->nullable()->after('genset');
            $table->boolean('rumah_panel')->nullable()->after('panel_listrik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('golongan_peralatan_mesin', function (Blueprint $table) {
            $table->dropColumn([
                'kategori',
                'nama_barang',
                'tahun_pembelian',
                'nomor_pabrik',
                'nomor_rangka',
                'nomor_mesin',
                'nomor_polisi',
                'bpkb',
                'kapasitas_listrik_kwh',
                'kapasitas_air',
                'head_tekanan',
                'merk_panel_pompa',
                'tipe_panel_pompa',
                'rtu',
                'kapasitas_listrik_va',
                'slo',
                'jil',
                'genset',
                'panel_listrik',
                'rumah_panel'
            ]);
        });
    }
}; 