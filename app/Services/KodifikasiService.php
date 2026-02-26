<?php


namespace App\Services;

use App\Models\Lokasi\SubBidang;
use App\Models\Tanah;
use Carbon\Carbon;

class KodifikasiService
{
    public static function kodeLokasi($wilayah_id, $sub_bidang_id, $unit_id, $tahun)
    {
        if (!$wilayah_id || !$sub_bidang_id || !$unit_id || !$tahun) {
            return null;
        }
        $subBidang = SubBidang::find($sub_bidang_id);
        return $wilayah_id . '.' . $subBidang->bidang->id_direktorat . '.' . $subBidang->id_bidang . '.' . $sub_bidang_id . '.' . $unit_id . '.' . substr($tahun, -2);
    }

    public static function kodeBarang($record, $sub_sub_kelompok_id, $tanggal_pengadaan, $class, $tahun_pembelian = null)
    {
        if (!$sub_sub_kelompok_id || (!$tanggal_pengadaan && !$tahun_pembelian)) {
            return null;
        }
        
        // Prioritize tahun_pembelian over tanggal_pengadaan
        if ($tahun_pembelian) {
            $tahun = substr($tahun_pembelian, -2);
        } else {
            try {
                $tahun = substr(Carbon::parse($tanggal_pengadaan)->format('Y'), -2);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        if ($record && str_starts_with($record->id, $sub_sub_kelompok_id . '.' . $tahun)) {
            return $record->id;
        }
        
        // Find the highest existing registration number for this sub_sub_kelompok_id and year
        try {
            $aset = $class::where('id', 'like', $sub_sub_kelompok_id . '.' . $tahun . '.%')->latest()->first();
        } catch (\Exception $e) {
            // If table doesn't exist or other database error, start with 0001
            $aset = null;
        }
        
        $kode_registrasi = '0001';
        
        if ($aset) {
            $kode_registrasi = sprintf("%04d", substr($aset->id, -4) + 1);
        }
        
        // Generate initial ID
        $generatedId = $sub_sub_kelompok_id . '.' . $tahun . '.' . $kode_registrasi;
        
        // Check if ID already exists and increment until unique
        $counter = intval($kode_registrasi);
        try {
            while ($class::where('id', $generatedId)->exists()) {
                $counter++;
                $kode_registrasi = sprintf("%04d", $counter);
                $generatedId = $sub_sub_kelompok_id . '.' . $tahun . '.' . $kode_registrasi;
            }
        } catch (\Exception $e) {
            // If database error occurs, just return the generated ID
            // The model's boot method will handle uniqueness
        }
        
        return $generatedId;
    }
}
