<?php

namespace App\Services;

use App\Models\PeralatanMesin;
use App\Models\Tanah;
use App\Models\Lokasi\Wilayah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WilayahMigrationService
{
    /**
     * Migrasi kode wilayah dan update kode_lokasi pada aset
     * Mataram: 01 -> 02
     * Lombok Barat: 02 -> 01
     */
    public static function migrateWilayahCodes()
    {
        try {
            DB::beginTransaction();
            
            // Update kode wilayah di tabel struktur_wilayah
            self::updateWilayahCodes();
            
            // Update kode_lokasi pada peralatan mesin
            self::updatePeralatanMesinKodeLokasi();
            
            // Update kode_lokasi pada tanah
            self::updateTanahKodeLokasi();
            
            DB::commit();
            
            Log::info('Migrasi kode wilayah berhasil dilakukan');
            return [
                'success' => true,
                'message' => 'Migrasi kode wilayah berhasil dilakukan'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat migrasi kode wilayah: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error saat migrasi kode wilayah: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update kode wilayah di tabel struktur_wilayah
     */
    private static function updateWilayahCodes()
    {
        // Gunakan temporary code untuk menghindari duplicate entry
        // Mataram: 01 -> 99 (temporary) -> 02
        // Lombok Barat: 02 -> 01
        // Mataram: 99 -> 02
        
        // Step 1: Update Mataram dari 01 ke temporary code 99
        DB::table('struktur_wilayah')
            ->where('id', '01')
            ->whereRaw('LOWER(wilayah) LIKE ?', ['%mataram%'])
            ->update(['id' => '99']);
            
        // Step 2: Update Lombok Barat dari 02 ke 01
        DB::table('struktur_wilayah')
            ->where('id', '02')
            ->whereRaw('LOWER(wilayah) LIKE ?', ['%lombok barat%'])
            ->update(['id' => '01']);
            
        // Step 3: Update Mataram dari temporary code 99 ke 02
        DB::table('struktur_wilayah')
            ->where('id', '99')
            ->whereRaw('LOWER(wilayah) LIKE ?', ['%mataram%'])
            ->update(['id' => '02']);
    }
    
    /**
     * Update kode_lokasi pada peralatan mesin
     */
    private static function updatePeralatanMesinKodeLokasi()
    {
        $peralatanMesin = PeralatanMesin::with(['wilayah', 'subBidang', 'unit'])
            ->whereNotNull('kode_lokasi')
            ->get();
            
        foreach ($peralatanMesin as $aset) {
            if (!$aset->wilayah || !$aset->subBidang || !$aset->unit || !$aset->tahun) {
                continue;
            }
            
            // Generate kode lokasi baru
            $newKodeLokasi = KodifikasiService::kodeLokasi(
                $aset->wilayah_id,
                $aset->sub_bidang_id,
                $aset->unit_id,
                $aset->tahun
            );
            
            if ($newKodeLokasi && $newKodeLokasi !== $aset->kode_lokasi) {
                // Update tanpa mengubah timestamps menggunakan query builder
                DB::table('golongan_peralatan_mesin')
                    ->where('id', $aset->id)
                    ->update(['kode_lokasi' => $newKodeLokasi]);
            }
        }
    }
    
    /**
     * Update kode_lokasi pada tanah
     */
    private static function updateTanahKodeLokasi()
    {
        $tanah = Tanah::with(['wilayah', 'subBidang', 'unit'])
            ->whereNotNull('kode_lokasi')
            ->get();
            
        foreach ($tanah as $aset) {
            if (!$aset->wilayah || !$aset->subBidang || !$aset->unit || !$aset->tahun) {
                continue;
            }
            
            // Generate kode lokasi baru
            $newKodeLokasi = KodifikasiService::kodeLokasi(
                $aset->wilayah_id,
                $aset->sub_bidang_id,
                $aset->unit_id,
                $aset->tahun
            );
            
            if ($newKodeLokasi && $newKodeLokasi !== $aset->kode_lokasi) {
                // Update tanpa mengubah timestamps menggunakan query builder
                DB::table('golongan_tanah')
                    ->where('id', $aset->id)
                    ->update(['kode_lokasi' => $newKodeLokasi]);
            }
        }
    }
    
    /**
     * Preview perubahan yang akan dilakukan
     */
    public static function previewMigration()
    {
        $preview = [
            'wilayah_changes' => [],
            'peralatan_mesin_changes' => [],
            'tanah_changes' => []
        ];
        
        // Preview perubahan wilayah
        $mataram = DB::table('struktur_wilayah')
            ->where('id', '01')
            ->whereRaw('LOWER(wilayah) LIKE ?', ['%mataram%'])
            ->first();
            
        if ($mataram) {
            $preview['wilayah_changes'][] = [
                'old_code' => '01',
                'new_code' => '02',
                'wilayah' => $mataram->wilayah
            ];
        }
        
        $lombokBarat = DB::table('struktur_wilayah')
            ->where('id', '02')
            ->whereRaw('LOWER(wilayah) LIKE ?', ['%lombok barat%'])
            ->first();
            
        if ($lombokBarat) {
            $preview['wilayah_changes'][] = [
                'old_code' => '02',
                'new_code' => '01',
                'wilayah' => $lombokBarat->wilayah
            ];
        }
        
        // Preview perubahan peralatan mesin
        $peralatanMesin = PeralatanMesin::with(['wilayah', 'subBidang', 'unit'])
            ->whereNotNull('kode_lokasi')
            ->get();
            
        foreach ($peralatanMesin as $aset) {
            if (!$aset->wilayah || !$aset->subBidang || !$aset->unit || !$aset->tahun) {
                continue;
            }
            
            $newKodeLokasi = KodifikasiService::kodeLokasi(
                $aset->wilayah_id,
                $aset->sub_bidang_id,
                $aset->unit_id,
                $aset->tahun
            );
            
            if ($newKodeLokasi && $newKodeLokasi !== $aset->kode_lokasi) {
                $preview['peralatan_mesin_changes'][] = [
                    'id' => $aset->id,
                    'old_kode_lokasi' => $aset->kode_lokasi,
                    'new_kode_lokasi' => $newKodeLokasi,
                    'wilayah' => $aset->wilayah->wilayah ?? 'N/A'
                ];
            }
        }
        
        // Preview perubahan tanah
        $tanah = Tanah::with(['wilayah', 'subBidang', 'unit'])
            ->whereNotNull('kode_lokasi')
            ->get();
            
        foreach ($tanah as $aset) {
            if (!$aset->wilayah || !$aset->subBidang || !$aset->unit || !$aset->tahun) {
                continue;
            }
            
            $newKodeLokasi = KodifikasiService::kodeLokasi(
                $aset->wilayah_id,
                $aset->sub_bidang_id,
                $aset->unit_id,
                $aset->tahun
            );
            
            if ($newKodeLokasi && $newKodeLokasi !== $aset->kode_lokasi) {
                $preview['tanah_changes'][] = [
                    'id' => $aset->id,
                    'old_kode_lokasi' => $aset->kode_lokasi,
                    'new_kode_lokasi' => $newKodeLokasi,
                    'wilayah' => $aset->wilayah->wilayah ?? 'N/A'
                ];
            }
        }
        
        return $preview;
    }
    
    /**
     * Update kode_lokasi menggunakan Eloquent tanpa timestamps (alternatif)
     */
    private static function updateKodeLokasiWithoutTimestamps($model, $id, $newKodeLokasi)
    {
        // Menggunakan withoutTimestamps untuk mencegah update timestamps
        $model::withoutTimestamps(function () use ($model, $id, $newKodeLokasi) {
            $model::where('id', $id)->update(['kode_lokasi' => $newKodeLokasi]);
        });
    }
    
    /**
     * Cek status migrasi
     */
    public static function checkMigrationStatus()
    {
        $status = [
            'mataram_code' => null,
            'lombok_barat_code' => null,
            'needs_migration' => false,
            'message' => ''
        ];
        
        // Cek kode Mataram
        $mataram = DB::table('struktur_wilayah')
            ->whereRaw('LOWER(wilayah) LIKE ?', ['%mataram%'])
            ->first();
            
        if ($mataram) {
            $status['mataram_code'] = $mataram->id;
        }
        
        // Cek kode Lombok Barat
        $lombokBarat = DB::table('struktur_wilayah')
            ->whereRaw('LOWER(wilayah) LIKE ?', ['%lombok barat%'])
            ->first();
            
        if ($lombokBarat) {
            $status['lombok_barat_code'] = $lombokBarat->id;
        }
        
        // Cek apakah perlu migrasi
        if ($status['mataram_code'] === '01' || $status['lombok_barat_code'] === '02') {
            $status['needs_migration'] = true;
            $status['message'] = 'Migrasi diperlukan: Mataram harus kode 02, Lombok Barat harus kode 01';
        } else {
            $status['message'] = 'Kode wilayah sudah benar';
        }
        
        return $status;
    }
    
    /**
     * Get statistik migrasi
     */
    public static function getMigrationStats()
    {
        $preview = self::previewMigration();
        
        return [
            'wilayah_changes_count' => count($preview['wilayah_changes']),
            'peralatan_mesin_changes_count' => count($preview['peralatan_mesin_changes']),
            'tanah_changes_count' => count($preview['tanah_changes']),
            'total_changes' => count($preview['wilayah_changes']) + 
                              count($preview['peralatan_mesin_changes']) + 
                              count($preview['tanah_changes'])
        ];
    }
} 