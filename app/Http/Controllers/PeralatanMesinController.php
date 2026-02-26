<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Lokasi\Ruangan;
use App\Models\PeralatanMesin;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PeralatanMesinKibExport;
use App\Exports\PeralatanMesinKirExport;
use App\Exports\PeralatanMesinRusakBeratExport;

class PeralatanMesinController extends Controller
{
    public function cetakKIR(Request $request)
    {
        $data = collect();
        $ruanganNama = null;
        $satuanKerjaNama = null;

        if ($request->filled('ruangan')) {
            $query = PeralatanMesin::with(['ruangan.unit', 'subSubKelompok.subKelompok'])
                ->where('ruangan_id', $request->ruangan);
            
            // Filter berdasarkan kategori KIB B
            if ($request->filled('kategori') && is_array($request->kategori)) {
                $query->whereIn('kategori', $request->kategori);
            }
            
            // Filter berdasarkan sub bidang
            if ($request->filled('sub_bidang_id')) {
                $query->where('sub_bidang_id', $request->sub_bidang_id);
            }
            
            // Filter berdasarkan unit
            if ($request->filled('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }
            
            $data = $query->get()
                ->groupBy(function($item) {
                    return $item->subSubKelompok?->subKelompok?->sub_kelompok ?? '-';
                });
            $ruangan = Ruangan::with('unit')->find($request->ruangan);
            $ruanganNama = $ruangan->nama ?? null;
            $satuanKerjaNama = $ruangan?->unit?->unit ?? null;
        }

        // Get penanggung jawab data if provided
        $penanggungJawab = null;
        if ($request->filled('penanggung_jawab')) {
            $penanggungJawab = User::find($request->penanggung_jawab);
        }
        
        // Handle asset grouping if enabled
        if ($request->filled('gabungkan_aset') && $request->filled('kolom_gabungan')) {
            $data = $this->groupSimilarAssets($data, $request->kolom_gabungan);
        }
        
        return view('filament.resources.peralatan-mesin-resource.kir-cetak', [
            'data' => $data,
            'ruanganNama' => $ruanganNama,
            'satuanKerjaNama' => $satuanKerjaNama,
            'request' => $request,
            'tanggalCetak' => $request->tanggal,
            'kategoriKib' => $request->kategori ?? [],
            'penanggungJawab' => $penanggungJawab,
        ]);
    }

    public function cetakKIB(Request $request)
    {
        $query = PeralatanMesin::with(['subSubKelompok', 'wilayah', 'subBidang', 'unit']);
        
        // Filter berdasarkan kategori KIB B
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        
        // Handle manual kode_lokasi input
        if ($request->filled('kode_lokasi')) {
            $query->where('kode_lokasi', $request->kode_lokasi);
        } else {
            // Handle dynamic filtering by individual fields
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
            if ($request->filled('tahun_pembelian')) {
                $query->where('tahun_pembelian', $request->tahun_pembelian);
            }
        }
        
        $data = $query->get();
        $subtotal = $data->sum('harga');
        
        // Get penanggung jawab data if provided
        $penanggungJawab = null;
        if ($request->filled('penanggung_jawab')) {
            $penanggungJawab = User::find($request->penanggung_jawab);
        }
        
        return view('filament.resources.peralatan-mesin-resource.kib-cetak', [
            'data' => $data,
            'subtotal' => $subtotal,
            'kategoriKib' => $request->kategori ?? 'Peralatan',
            'penanggungJawab' => $penanggungJawab,
        ]);
    }

    public function exportKIB(Request $request)
    {
        $query = PeralatanMesin::with(['subSubKelompok', 'wilayah', 'subBidang', 'unit']);
        
        // Filter berdasarkan kategori KIB B
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        
        // Handle manual kode_lokasi input
        if ($request->filled('kode_lokasi')) {
            $query->where('kode_lokasi', $request->kode_lokasi);
        } else {
            // Handle dynamic filtering by individual fields
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
            if ($request->filled('tahun_pembelian')) {
                $query->where('tahun_pembelian', $request->tahun_pembelian);
            }
        }
        
        $data = $query->get();
        $kategori = $request->kategori ?? 'Peralatan';
        
        return Excel::download(new PeralatanMesinKibExport($data, $kategori), 'peralatan-mesin-kib-' . strtolower(str_replace(' ', '-', $kategori)) . '-' . date('Y-m-d') . '.xlsx');
    }

    public function exportKIR(Request $request)
    {
        $data = collect();
        
        if (!$request->filled('ruangan')) {
            // Return empty export if no ruangan selected
            return Excel::download(new PeralatanMesinKirExport(collect()), 'peralatan-mesin-kir-' . date('Y-m-d') . '.xlsx');
        }
        
        $query = PeralatanMesin::with(['ruangan.unit', 'subSubKelompok.subKelompok'])
            ->where('ruangan_id', $request->ruangan);
        
        // Filter berdasarkan kategori KIB B - handle both array and single value
        $kategori = $request->input('kategori');
        if (!empty($kategori)) {
            if (is_array($kategori)) {
                $query->whereIn('kategori', $kategori);
            } else {
                $query->where('kategori', $kategori);
            }
        }
        
        // Filter berdasarkan sub bidang
        if ($request->filled('sub_bidang_id')) {
            $query->where('sub_bidang_id', $request->sub_bidang_id);
        }
        
        // Filter berdasarkan unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        $data = $query->get()
            ->groupBy(function($item) {
                return $item->subSubKelompok?->subKelompok?->sub_kelompok ?? '-';
            });
        
        // Handle asset grouping if enabled
        if ($request->filled('gabungkan_aset') && $request->filled('kolom_gabungan')) {
            $kolomGabungan = $request->input('kolom_gabungan');
            if (is_array($kolomGabungan)) {
                $data = $this->groupSimilarAssets($data, $kolomGabungan);
            } else {
                $data = $this->groupSimilarAssets($data, [$kolomGabungan]);
            }
        }
        
        return Excel::download(new PeralatanMesinKirExport($data), 'peralatan-mesin-kir-' . date('Y-m-d') . '.xlsx');
    }

    public function exportRusakBerat(Request $request)
    {
        $query = PeralatanMesin::with(['subSubKelompok', 'wilayah', 'subBidang.bidang.direktorat', 'unit']);
        
        // Filter hanya barang dengan kondisi Rusak Berat
        $query->where('kondisi', 'Rusak Berat');
        
        // Handle manual kode_lokasi input
        if ($request->filled('kode_lokasi')) {
            $query->where('kode_lokasi', $request->kode_lokasi);
        } else {
            // Handle dynamic filtering by individual fields
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
            if ($request->filled('tahun_pembelian')) {
                $query->where('tahun_pembelian', $request->tahun_pembelian);
            }
        }
        
        $data = $query->get();
        
        // Group by kategori
        $groups = collect();
        $kendaraanDinas = $data->filter(function($item) {
            return $item->kategori === 'Kendaraan Dinas';
        });
        $peralatan = $data->filter(function($item) {
            return $item->kategori === 'Peralatan';
        });
        $pompa = $data->filter(function($item) {
            return $item->kategori === 'Pompa';
        });
        
        $label = 'A';
        if ($kendaraanDinas->count()) {
            $groups["$label. KENDARAAN DINAS"] = $kendaraanDinas;
            $label++;
        }
        if ($peralatan->count()) {
            $groups["$label. PERALATAN DAN MESIN"] = $peralatan;
            $label++;
        }
        if ($pompa->count()) {
            $groups["$label. POMPA"] = $pompa;
        }
        
        return Excel::download(new PeralatanMesinRusakBeratExport($groups), 'peralatan-mesin-rusak-berat-' . date('Y-m-d') . '.xlsx');
    }

    public function cetakRusakBerat(Request $request)
    {
        $query = PeralatanMesin::with(['subSubKelompok', 'wilayah', 'subBidang.bidang.direktorat', 'unit']);
        
        // Filter hanya barang dengan kondisi Rusak Berat
        $query->where('kondisi', 'Rusak Berat');
        
        // Initialize variables for administrative info
        $wilayah = null;
        $direktorat = null;
        $bidang = null;
        $subBidang = null;
        $unit = null;
        $kodeLokasi = null;
        
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
                if ($bidang) {
                    $direktorat = \App\Models\Lokasi\Direktorat::where('id', $bidang->id_direktorat)->first();
                }
            }
            if ($request->filled('sub_bidang_id')) {
                $query->where('sub_bidang_id', $request->sub_bidang_id);
                $subBidang = \App\Models\Lokasi\SubBidang::where('id', $request->sub_bidang_id)->first();
                if ($subBidang) {
                    $bidang = \App\Models\Lokasi\Bidang::where('id', $subBidang->id_bidang)->first();
                    if ($bidang) {
                        $direktorat = \App\Models\Lokasi\Direktorat::where('id', $bidang->id_direktorat)->first();
                    }
                }
            }
            if ($request->filled('unit_id')) {
                $query->where('unit_id', $request->unit_id);
                $unit = \App\Models\Lokasi\Unit::where('id', $request->unit_id)->first();
            }
            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }
            if ($request->filled('tahun_pembelian')) {
                $query->where('tahun_pembelian', $request->tahun_pembelian);
            }
            
            // Get kode lokasi only if all required filters are provided
            // Kode lokasi akan di-generate dari filter yang dipilih jika semua komponen tersedia
            if ($request->filled('wilayah_id') && $request->filled('sub_bidang_id') && $request->filled('unit_id') && $request->filled('tahun')) {
                $kodeLokasi = \App\Services\KodifikasiService::kodeLokasi(
                    $request->wilayah_id,
                    $request->sub_bidang_id,
                    $request->unit_id,
                    $request->tahun
                );
            }
        }
        
        $data = $query->get();
        
        // Group by kategori
        $groups = collect();
        $kendaraanDinas = $data->filter(function($item) {
            return $item->kategori === 'Kendaraan Dinas';
        });
        $peralatan = $data->filter(function($item) {
            return $item->kategori === 'Peralatan';
        });
        $pompa = $data->filter(function($item) {
            return $item->kategori === 'Pompa';
        });
        
        $label = 'A';
        if ($kendaraanDinas->count()) {
            $groups["$label. KENDARAAN DINAS"] = $kendaraanDinas;
            $label++;
        }
        if ($peralatan->count()) {
            $groups["$label. PERALATAN DAN MESIN"] = $peralatan;
            $label++;
        }
        if ($pompa->count()) {
            $groups["$label. POMPA"] = $pompa;
        }
        
        // Calculate subtotals per category
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
        
        return view('filament.resources.peralatan-mesin-resource.rusak-berat-cetak', [
            'grouped' => $groups,
            'kodeLokasi' => $kodeLokasi,
            'subtotals' => $subtotals,
            'total' => $total,
            'penanggungJawab' => $penanggungJawab,
            'wilayah' => $wilayah,
            'direktorat' => $direktorat,
            'bidang' => $bidang,
            'subBidang' => $subBidang,
            'unit' => $unit,
        ]);
    }
    
    private function groupSimilarAssets($data, $groupingColumns)
    {
        $groupedData = collect();
        
        foreach ($data as $groupName => $items) {
            $groupedItems = collect();
            $groupedAssets = [];
            
            foreach ($items as $item) {
                // Create a key based on selected columns
                $key = $this->createGroupingKey($item, $groupingColumns);
                
                if (!isset($groupedAssets[$key])) {
                    $groupedAssets[$key] = [
                        'items' => collect(),
                        'keterangan' => collect(),
                        'kondisi_counts' => [
                            'Baik' => 0,
                            'Kurang Baik' => 0,
                            'Rusak Berat' => 0
                        ]
                    ];
                }
                
                $groupedAssets[$key]['items']->push($item);
                
                // Collect keterangan
                if (!empty($item->keterangan)) {
                    $groupedAssets[$key]['keterangan']->push($item->keterangan);
                }
                
                // Count kondisi
                if (isset($item->kondisi)) {
                    $groupedAssets[$key]['kondisi_counts'][$item->kondisi]++;
                }
            }
            
            // Create merged items
            foreach ($groupedAssets as $key => $groupData) {
                $firstItem = $groupData['items']->first();
                $mergedItem = clone $firstItem;
                
                // Set jumlah
                $mergedItem->jumlah = $groupData['items']->count();
                
                // Set harga perolehan (sum of all items)
                $mergedItem->harga = $groupData['items']->sum('harga');
                
                // Set kondisi based on majority
                $kondisiCounts = $groupData['kondisi_counts'];
                $mergedItem->kondisi = array_keys($kondisiCounts, max($kondisiCounts))[0];
                
                // Set keterangan (merged with bullets)
                $keterangan = $groupData['keterangan']->unique()->values();
                if ($keterangan->count() > 1) {
                    $mergedItem->keterangan = $keterangan->map(function($ket) {
                        return "• " . $ket;
                    })->implode("\n");
                } else {
                    $mergedItem->keterangan = $keterangan->first() ?? '';
                }
                
                // Add grouping info for display
                $mergedItem->grouping_info = [
                    'total_items' => $groupData['items']->count(),
                    'keterangan_count' => $keterangan->count(),
                    'kondisi_breakdown' => $kondisiCounts
                ];
                
                $groupedItems->push($mergedItem);
            }
            
            $groupedData[$groupName] = $groupedItems;
        }
        
        return $groupedData;
    }
    
    private function createGroupingKey($item, $columns)
    {
        $keyParts = [];
        
        foreach ($columns as $column) {
            switch ($column) {
                case 'merek':
                    $keyParts[] = $item->merek ?? '';
                    break;
                case 'no_seri_pabrik':
                    $keyParts[] = $item->no_seri_pabrik ?? '';
                    break;
                case 'spesifikasi':
                    $keyParts[] = $item->spesifikasi ?? '';
                    break;
                case 'bahan':
                    $keyParts[] = $item->bahan ?? '';
                    break;
                case 'tahun':
                    $keyParts[] = $item->tahun ?? '';
                    break;
            }
        }
        
        return implode('|', $keyParts);
    }

    public function cetakQR(Request $request)
    {
        $ids = explode(',', $request->ids);
        $data = PeralatanMesin::with(['subSubKelompok', 'ruangan', 'wilayah', 'subBidang', 'unit'])
            ->whereIn('id', $ids)
            ->get();

        $paperSize = $request->get('paper_size', '10x5'); // Default to 10x5cm

        // Check if thermal printer mode is requested
        if ($request->has('thermal') && $request->thermal == 'true') {
            return view('filament.resources.peralatan-mesin-resource.qr-cetak-thermal', [
                'data' => $data,
                'paper_size' => $paperSize,
            ]);
        }

        // Route to appropriate template based on paper size
        if ($paperSize === '10x5') {
            return view('filament.resources.peralatan-mesin-resource.qr-cetak-10x5', [
                'data' => $data,
                'paper_size' => $paperSize,
            ]);
        } else {
            // Default to 10x2cm (current template)
            return view('filament.resources.peralatan-mesin-resource.qr-cetak', [
                'data' => $data,
                'paper_size' => $paperSize,
            ]);
        }
    }

    public function cetakQRZPL(Request $request)
    {
        $ids = explode(',', $request->ids);
        $data = PeralatanMesin::with(['subSubKelompok', 'ruangan', 'wilayah', 'subBidang', 'unit'])
            ->whereIn('id', $ids)
            ->get();

        $zplCommands = [];
        
        foreach ($data as $record) {
            // Generate QR code as base64
            try {
                $logoPath = public_path('images/ptam.png');
                if (file_exists($logoPath)) {
                    $qrCodeWithLogo = QrCode::format('png')
                        ->size(150)
                        ->errorCorrection('H')
                        ->margin(0)
                        ->merge($logoPath, 0.3, true)
                        ->generate(route('public.peralatan-mesin.detail', $record->id));
                } else {
                    $qrCodeWithLogo = QrCode::format('png')
                        ->size(150)
                        ->errorCorrection('H')
                        ->margin(0)
                        ->generate(route('public.peralatan-mesin.detail', $record->id));
                }
            } catch (\Exception $e) {
                $qrCodeWithLogo = QrCode::format('png')
                    ->size(150)
                    ->errorCorrection('H')
                    ->margin(0)
                    ->generate(route('public.peralatan-mesin.detail', $record->id));
            }

            // Convert QR code to ZPL format
            $qrCodeBase64 = base64_encode($qrCodeWithLogo);
            
            // ZPL Command for 83mm x 28mm label
            $zpl = "^XA"; // Start ZPL
            $zpl .= "^MMT"; // Print mode
            $zpl .= "^PW831"; // Print width (83mm = 831 dots at 203 DPI)
            $zpl .= "^LL1102"; // Label length (28mm = 1102 dots at 203 DPI)
            $zpl .= "^LS0"; // Left margin
            $zpl .= "^BY2"; // Barcode width
            $zpl .= "^FO50,50"; // Field origin (QR code position)
            $zpl .= "^GFA," . strlen($qrCodeBase64) . "," . strlen($qrCodeBase64) . "," . strlen($qrCodeBase64) . "," . $qrCodeBase64; // Graphic field
            $zpl .= "^FO300,50"; // Text position
            $zpl .= "^A0N,30,30"; // Font
            $zpl .= "^FD" . $record->kode_lokasi . "^FS"; // Kode lokasi
            $zpl .= "^FO300,100"; // Next text position
            $zpl .= "^A0N,30,30"; // Font
            $zpl .= "^FD" . $record->id . "^FS"; // Kode aset
            $zpl .= "^FO300,150"; // Next text position
            $zpl .= "^A0N,20,20"; // Smaller font
            $zpl .= "^FD" . ($record->subSubKelompok->sub_sub_kelompok ?? 'N/A') . "^FS"; // Sub sub kelompok
            $zpl .= "^XZ"; // End ZPL
            
            $zplCommands[] = $zpl;
        }

        return response()->json([
            'zpl_commands' => $zplCommands,
            'total_labels' => count($zplCommands)
        ]);
    }
} 