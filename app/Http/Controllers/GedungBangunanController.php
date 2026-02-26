<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GedungBangunan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GedungBangunanKibExport;

class GedungBangunanController extends Controller
{
    public function cetakKIB(Request $request)
    {
        $query = GedungBangunan::with(['subSubKelompok', 'wilayah', 'subBidang.bidang.direktorat', 'unit']);
        
        // Handle manual kode_lokasi input
        if ($request->filled('kode_lokasi')) {
            $kodeLokasi = $request->kode_lokasi;
            $direktorat = \App\Models\Lokasi\Direktorat::where('kode_lokasi', substr($kodeLokasi, 0, 2))->first();
            $bidang = \App\Models\Lokasi\Bidang::where('kode_lokasi', substr($kodeLokasi, 0, 4))->first();
            $subBidang = \App\Models\Lokasi\SubBidang::where('kode_lokasi', substr($kodeLokasi, 0, 6))->first();
            $unit = \App\Models\Lokasi\Unit::where('kode_lokasi', substr($kodeLokasi, 0, 8))->first();
            $query->where('kode_lokasi', $request->kode_lokasi);
        } else {
            // Handle dynamic filtering by individual fields
            if ($request->filled('wilayah_id')) {
                $query->where('wilayah_id', $request->wilayah_id);
                $wilayah = \App\Models\Lokasi\Wilayah::where('id', $request->wilayah_id)->first();  
            }
            if ($request->filled('bidang_id')) {
                $query->whereHas('subBidang', function($q) use ($request) {
                    $q->where('id_bidang', $request->bidang_id);
                });
                $bidang = \App\Models\Lokasi\Bidang::where('id', $request->bidang_id)->first();
                $direktorat = \App\Models\Lokasi\Direktorat::where('id', $bidang->id_direktorat)->first();
            }
            if ($request->filled('sub_bidang_id')) {
                $query->where('sub_bidang_id', $request->sub_bidang_id);
                $subBidang = \App\Models\Lokasi\SubBidang::where('id', $request->sub_bidang_id)->first();
                $bidang = \App\Models\Lokasi\Bidang::where('id', $subBidang->id_bidang)->first();
                $direktorat = \App\Models\Lokasi\Direktorat::where('id', $bidang->id_direktorat)->first();
            }
            if ($request->filled('unit_id')) {
                $query->where('unit_id', $request->unit_id);
                $unit = \App\Models\Lokasi\Unit::where('id', $request->unit_id)->first();
            }
            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }
        }
        
        $data = $query->get();
        $subtotal = $data->sum('harga');
        
        // Get penanggung jawab data if provided
        $penanggungJawab = null;
        if ($request->filled('penanggung_jawab')) {
            $penanggungJawab = User::find($request->penanggung_jawab);
        }
        
        return view('filament.resources.gedung-bangunan-resource.kib-cetak', [
            'data' => $data,
            'subtotal' => $subtotal,
            'penanggungJawab' => $penanggungJawab,
            'direktorat' => $direktorat ?? null,
            'bidang' => $bidang ?? null,
            'subBidang' => $subBidang ?? null,
            'unit' => $unit ?? null,
            'wilayah' => $wilayah ?? null,
        ]);
    }

    public function cetakIdle(Request $request)
    {
        $query = GedungBangunan::with(['subSubKelompok', 'wilayah', 'subBidang'])
            ->where('is_idle', true); // Hanya gedung bangunan idle
        
        // Get info for header
        $direktorat = null;
        $bidang = null;
        $subBidang = null;
        $lokasiUnitKerja = null;
        $kodeLokasi = 'Semua Lokasi';
        
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
            $wilayahModel = \App\Models\Lokasi\Wilayah::find($request->wilayah_id);
            if ($wilayahModel) {
                $kodeLokasi = $wilayahModel->wilayah;
            }
        }
        
        if ($request->filled('bidang_id')) {
            $query->whereHas('subBidang', function($q) use ($request) {
                $q->where('id_bidang', $request->bidang_id);
            });
            $bidangModel = \App\Models\Lokasi\Bidang::find($request->bidang_id);
            if ($bidangModel) {
                $bidang = $bidangModel->bidang;
                $direktoratModel = \App\Models\Lokasi\Direktorat::find($bidangModel->id_direktorat);
                if ($direktoratModel) {
                    $direktorat = $direktoratModel->direktorat;
                }
            }
        }
        
        if ($request->filled('sub_bidang_id')) {
            $query->where('sub_bidang_id', $request->sub_bidang_id);
            $subBidangModel = \App\Models\Lokasi\SubBidang::find($request->sub_bidang_id);
            if ($subBidangModel) {
                $subBidang = $subBidangModel->sub_bidang;
                $bidangModel = \App\Models\Lokasi\Bidang::find($subBidangModel->id_bidang);
                if ($bidangModel) {
                    $bidang = $bidangModel->bidang;
                    $direktoratModel = \App\Models\Lokasi\Direktorat::find($bidangModel->id_direktorat);
                    if ($direktoratModel) {
                        $direktorat = $direktoratModel->direktorat;
                    }
                }
            }
        }
        
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
            $unitModel = \App\Models\Lokasi\Unit::find($request->unit_id);
            if ($unitModel) {
                $lokasiUnitKerja = $unitModel->unit;
            }
        }
        
        $data = $query->orderBy('id')->get();
        $total = $data->sum(function($item) {
            return $item->harga ?? 0;
        });
        
        // Get penanggung jawab data if provided
        $penanggungJawab = null;
        if ($request->filled('penanggung_jawab')) {
            $penanggungJawab = User::find($request->penanggung_jawab);
        }
        
        return view('filament.resources.gedung-bangunan-resource.idle-cetak', [
            'data' => $data,
            'direktorat' => $direktorat,
            'bidang' => $bidang,
            'subBidang' => $subBidang,
            'lokasiUnitKerja' => $lokasiUnitKerja,
            'kodeLokasi' => $kodeLokasi,
            'total' => $total,
            'penanggungJawab' => $penanggungJawab,
        ]);
    }

    public function exportKIB(Request $request)
    {
        $query = GedungBangunan::with(['subSubKelompok', 'wilayah', 'subBidang.bidang.direktorat', 'unit']);
        
        if ($request->filled('kode_lokasi')) {
            $query->where('kode_lokasi', $request->kode_lokasi);
        } else {
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
            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }
        }
        
        $data = $query->get();
        
        return Excel::download(new GedungBangunanKibExport($data), 'gedung-bangunan-kib-' . date('Y-m-d') . '.xlsx');
    }

    public function exportIdle(Request $request)
    {
        $query = GedungBangunan::with(['subSubKelompok', 'wilayah', 'subBidang'])
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
        
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        $data = $query->orderBy('id')->get();
        
        return Excel::download(new GedungBangunanKibExport($data, 'idle'), 'gedung-bangunan-idle-' . date('Y-m-d') . '.xlsx');
    }
} 