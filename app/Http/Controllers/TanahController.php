<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tanah;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TanahKibExport;

class TanahController extends Controller
{
    public function cetakKIB(Request $request)
    {
        $query = Tanah::with('wilayah','subSubKelompok');
        
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        
        if ($request->filled('bidang_id')) {
            $query->whereHas('subBidang', function($q) use ($request) {
                $q->where('id_bidang', $request->bidang_id);
            });
        }
        
        if ($request->filled('sub_bidang_id')) {
            $query->where('sub_bidang_id', $request->sub_bidang_id);
        }
        
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->filled('sub_sub_kelompok_id')) {
            $query->where('sub_sub_kelompok_id', $request->sub_sub_kelompok_id);
        }
        
        $data = $query->get();
        $groups = [];
        $mataram = $data->filter(function($item) {
            return $item->wilayah && stripos($item->wilayah->wilayah, 'mataram') !== false;
        });
        $lombokBarat = $data->filter(function($item) {
            return $item->wilayah && stripos($item->wilayah->wilayah, 'lombok barat') !== false;
        });
        $lainnya = $data->filter(function($item) {
            return !($item->wilayah && (stripos($item->wilayah->wilayah, 'mataram') !== false || stripos($item->wilayah->wilayah, 'lombok barat') !== false));
        });
        $label = 'A';
        if ($lombokBarat->count()) {
            $groups["$label. KABUPATEN LOMBOK BARAT"] = $lombokBarat;
            $label++;
        }
        if ($mataram->count()) {
            $groups["$label. KOTA MATARAM"] = $mataram;
            $label++;
        }
        if ($lainnya->count()) {
            $groups["$label. LAINNYA"] = $lainnya;
        }
        $subtotals = [];
        $total = 0;
        foreach ($groups as $key => $items) {
            $subtotal = $items->sum('harga');
            $subtotals[$key] = $subtotal;
            $total += $subtotal;
        }
        // Get penanggung jawab data if provided
        $penanggungJawab = null;
        if ($request->filled('penanggung_jawab')) {
            $penanggungJawab = User::find($request->penanggung_jawab);
        }
        
        return view('filament.resources.tanah-resource.kib-cetak', [
            'grouped' => $groups,
            'kodeLokasi' => $request->kode_lokasi ?? 'Semua Lokasi',
            'subtotals' => $subtotals,
            'total' => $total,
            'penanggungJawab' => $penanggungJawab,
        ]);
    }

    public function cetakIdle(Request $request)
    {
        $query = Tanah::with('wilayah', 'subBidang', 'subSubKelompok')
            ->where('is_idle', true); // Hanya tanah idle
        
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        
        if ($request->filled('bidang_id')) {
            $query->whereHas('subBidang', function($q) use ($request) {
                $q->where('id_bidang', $request->bidang_id);
            });
        }
        
        if ($request->filled('sub_bidang_id')) {
            $query->where('sub_bidang_id', $request->sub_bidang_id);
        }
        
        if ($request->filled('sub_sub_kelompok_id')) {
            $query->where('sub_sub_kelompok_id', $request->sub_sub_kelompok_id);
        }
        
        $data = $query->orderBy('id')->get();
        
        // Get info for header
        $direktorat = null;
        $bidang = null;
        $subBidang = null;
        $lokasiUnitKerja = null;
        $kodeLokasi = 'Semua Lokasi';
        
        if ($request->filled('sub_bidang_id')) {
            $subBidangModel = \App\Models\Lokasi\SubBidang::find($request->sub_bidang_id);
            if ($subBidangModel) {
                $subBidang = $subBidangModel->sub_bidang;
                $bidangModel = \App\Models\Lokasi\Bidang::find($subBidangModel->id_bidang);
                if ($bidangModel) {
                    $bidang = $bidangModel->bidang;
                }
            }
        } elseif ($request->filled('bidang_id')) {
            $bidangModel = \App\Models\Lokasi\Bidang::find($request->bidang_id);
            if ($bidangModel) {
                $bidang = $bidangModel->bidang;
            }
        }
        
        if ($request->filled('wilayah_id')) {
            $wilayahModel = \App\Models\Lokasi\Wilayah::find($request->wilayah_id);
            if ($wilayahModel) {
                $kodeLokasi = $wilayahModel->wilayah;
            }
        }
        
        // Get penanggung jawab data if provided
        $penanggungJawab = null;
        if ($request->filled('penanggung_jawab')) {
            $penanggungJawab = User::find($request->penanggung_jawab);
        }
        
        return view('filament.resources.tanah-resource.idle-cetak', [
            'data' => $data,
            'direktorat' => $direktorat,
            'bidang' => $bidang,
            'subBidang' => $subBidang,
            'lokasiUnitKerja' => $lokasiUnitKerja,
            'kodeLokasi' => $kodeLokasi,
            'penanggungJawab' => $penanggungJawab,
        ]);
    }

    public function exportKIB(Request $request)
    {
        $query = Tanah::with('wilayah','subSubKelompok');
        
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        
        if ($request->filled('bidang_id')) {
            $query->whereHas('subBidang', function($q) use ($request) {
                $q->where('id_bidang', $request->bidang_id);
            });
        }
        
        if ($request->filled('sub_bidang_id')) {
            $query->where('sub_bidang_id', $request->sub_bidang_id);
        }
        
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->filled('sub_sub_kelompok_id')) {
            $query->where('sub_sub_kelompok_id', $request->sub_sub_kelompok_id);
        }
        
        $data = $query->get();
        
        return Excel::download(new TanahKibExport($data), 'tanah-kib-' . date('Y-m-d') . '.xlsx');
    }

    public function exportIdle(Request $request)
    {
        $query = Tanah::with('wilayah', 'subBidang', 'subSubKelompok')
            ->where('is_idle', true);
        
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        
        if ($request->filled('bidang_id')) {
            $query->whereHas('subBidang', function($q) use ($request) {
                $q->where('id_bidang', $request->bidang_id);
            });
        }
        
        if ($request->filled('sub_bidang_id')) {
            $query->where('sub_bidang_id', $request->sub_bidang_id);
        }
        
        if ($request->filled('sub_sub_kelompok_id')) {
            $query->where('sub_sub_kelompok_id', $request->sub_sub_kelompok_id);
        }
        
        $data = $query->orderBy('id')->get();
        
        return Excel::download(new TanahKibExport($data, 'idle'), 'tanah-idle-' . date('Y-m-d') . '.xlsx');
    }
} 