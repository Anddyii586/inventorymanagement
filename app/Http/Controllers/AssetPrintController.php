<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tanah;
use App\Models\PeralatanMesin;
use App\Models\GedungBangunan;
use App\Models\Jaringan;
use App\Models\AsetTetapLainnya;
use App\Models\KonstruksiDalamPengerjaan;

class AssetPrintController extends Controller
{
    public function bulkPrint(Request $request)
    {
        $type = $request->get('type');
        $ids = $request->get('ids') ? explode(',', $request->get('ids')) : [];
        $printNew = $request->has('new');

        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            abort(404, 'Asset type not found');
        }

        $query = $modelClass::query();

        if ($printNew) {
            $query->whereDate('created_at', now()->toDateString());
        } elseif (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            abort(400, 'No assets selected for printing');
        }

        $data = $query->with(['subSubKelompok', 'ruangan', 'wilayah', 'subBidang', 'unit'])->get();

        if ($data->isEmpty()) {
            return "<script>alert('Tidak ada aset yang ditemukan untuk dicetak (pastikan ada aset yang dibuat hari ini jika menggunakan fitur Aset Baru).'); window.close();</script>";
        }

        $assetTypeName = $this->getAssetTypeName($type);
        $routeName = $this->getRouteName($type);

        return view('filament.resources.common.qr-bulk-print', [
            'data' => $data,
            'assetTypeName' => $assetTypeName,
            'routeName' => $routeName,
        ]);
    }

    private function getModelClass($type)
    {
        return match ($type) {
            'tanah' => Tanah::class,
            'peralatan-mesin' => PeralatanMesin::class,
            'gedung-bangunan' => GedungBangunan::class,
            'jaringan' => Jaringan::class,
            'aset-tetap-lainnya' => AsetTetapLainnya::class,
            'konstruksi-dalam-pengerjaan' => KonstruksiDalamPengerjaan::class,
            default => null,
        };
    }

    private function getAssetTypeName($type)
    {
        return match ($type) {
            'tanah' => 'TANAH',
            'peralatan-mesin' => 'PERALATAN & MESIN',
            'gedung-bangunan' => 'GEDUNG & BANGUNAN',
            'jaringan' => 'JARINGAN',
            'aset-tetap-lainnya' => 'ASET TETAP LAINNYA',
            'konstruksi-dalam-pengerjaan' => 'KONSTRUKSI DALAM PENGERJAAN',
            default => 'ASET',
        };
    }

    private function getRouteName($type)
    {
        return match ($type) {
            'tanah' => 'public.tanah.detail',
            'peralatan-mesin' => 'public.peralatan-mesin.detail',
            'gedung-bangunan' => 'public.gedung-bangunan.detail',
            'jaringan' => 'public.jaringan.detail',
            'aset-tetap-lainnya' => 'public.aset-tetap-lainnya.detail',
            'konstruksi-dalam-pengerjaan' => 'public.konstruksi-dalam-pengerjaan.detail',
            default => 'public.detail',
        };
    }

    public function printConditionReport(Request $request)
    {
        $query = PeralatanMesin::query();

        // Filter by condition if provided
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        // Search query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_lokasi', 'like', "%{$search}%")
                  ->orWhere('merek', 'like', "%{$search}%");
            });
        }

        $data = $query->with(['subSubKelompok', 'ruangan', 'wilayah', 'subBidang', 'unit'])
                      ->orderBy('id', 'desc')
                      ->get();

        return view('filament.pages.print.asset-condition-report', [
            'data' => $data,
            'kondisi' => $request->kondisi ?? 'Semua Kondisi',
        ]);
    }
}
