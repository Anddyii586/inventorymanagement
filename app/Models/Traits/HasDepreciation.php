<?php

namespace App\Models\Traits;

use App\Models\Aset\SubSubKelompok;
use Carbon\Carbon;

trait HasDepreciation
{
    /**
     * Calculate current book value based on straight line depreciation
     * Formula: Book Value = Cost - ( (Cost / Economic Life) * Years Used )
     */
    public function calculateDepreciation()
    {
        // 1. Get Economic Life from Category
        $category = SubSubKelompok::find($this->sub_sub_kelompok_id);
        
        if (!$category || !$category->umur_ekonomis || $category->umur_ekonomis <= 0) {
            // Cannot calculate if no economic life. Default to price.
            $this->nilai_buku = $this->harga;
            $this->tanggal_penyusutan_terakhir = now();
            $this->saveQuietly();
            return;
        }

        $umurEkonomis = $category->umur_ekonomis;
        $hargaPerolehan = $this->harga;
        
        // 2. Calculate Age in Years
        // Use tanggal_pengadaan if available, otherwise created_at, otherwise year (if available)
        $startDate = null;
        if ($this->tanggal_pengadaan) {
            $startDate = Carbon::parse($this->tanggal_pengadaan);
        } elseif ($this->tahun_pembelian) {
            $startDate = Carbon::createFromDate($this->tahun_pembelian, 1, 1);
        } else {
            $startDate = $this->created_at;
        }

        if (!$startDate) {
             $this->nilai_buku = $hargaPerolehan;
             $this->saveQuietly();
             return;
        }

        // Calculate full years passed
        // Or should we use monthly? Usually straight line is annual.
        // Let's use float years for more precision: days / 365
        $yearsUsed = $startDate->diffInDays(now()) / 365.25;

        // 3. Calculate Depreciation
        $yearlyDepreciation = $hargaPerolehan / $umurEkonomis;
        $totalDepreciation = $yearlyDepreciation * $yearsUsed;

        $nilaiBuku = $hargaPerolehan - $totalDepreciation;

        // Ensure not negative
        if ($nilaiBuku < 0) {
            $nilaiBuku = 0;
        }

        // 4. Update Record
        $this->nilai_buku = round($nilaiBuku, 2);
        $this->tanggal_penyusutan_terakhir = now();
        $this->saveQuietly();

        return $this->nilai_buku;
    }
}
