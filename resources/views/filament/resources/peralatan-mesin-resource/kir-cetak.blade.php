@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Inventaris Ruangan (KIR)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; }
        .table th, .table td { vertical-align: middle !important; }
        .footer-sign { height: 80px; }
        .footer-table td, .footer-table th { padding: 0 !important; font-size: 11px; border: none !important; }
        .signature-section { margin-top: 50px; }
        .signature-line { border-bottom: 1px dotted #000; min-width: 200px; display: inline-block; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="text-center my-3">
        <img src="/images/logo.png" alt="Logo" style="height:120px;">
        <h5 class="fw-bold mt-2">KARTU INVENTARIS RUANGAN (KIR)</h5>
    </div>
    @if($data && count($data))
        <table class="table table-borderless footer-table" style="width:auto">
            <tr>
                <td>Unit Kerja</td>
                <td class="text-center" width="10px">:</td>
                <td>PTAM Giri Menang</td>
            </tr>
            <tr>
                <td>Satuan Kerja</td>
                <td class="text-center" width="10px">:</td>
                <td>{{ $satuanKerjaNama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Ruangan</td>
                <td class="text-center" width="10px">:</td>
                <td>{{ $ruanganNama ?? '-' }}</td>
            </tr>
            @if(!empty($kategoriKib))
            <tr>
                <td>Kategori</td>
                <td class="text-center" width="10px">:</td>
                <td>{{ implode(', ', $kategoriKib) }}</td>
            </tr>
            @endif
        </table>
        <table class="table table-sm align-middle table-bordered mb-1" style="border:1px solid #dee2e6">
            <thead class="table-light">
            <tr class="text-center">
                <th rowspan="2">No</th>
                <th rowspan="2">Jenis Barang</th>
                <th rowspan="2">Merk/Model</th>
                <th rowspan="2">No Seri Pabrik</th>
                <th rowspan="2">Ukuran</th>
                <th rowspan="2">Bahan</th>
                <th rowspan="2">Tahun Pembelian</th>
                <th rowspan="2">Kode Lokasi</th>
                <th rowspan="2">Kode Barang</th>
                <th rowspan="2">Jumlah</th>
                <th rowspan="2">Harga Perolehan</th>
                <th colspan="3">Keadaan Barang</th>
                <th rowspan="2">Keterangan Mutasi dan lain lain</th>
                <th rowspan="2">Kode Dokumentasi</th>
            </tr>
            <tr class="text-center">
                <th>Baik</th>
                <th>Kurang Baik</th>
                <th>Rusak Berat</th>
            </tr>
            </thead>
            <tbody>
            @php $no = 1; @endphp
            @foreach($data as $group => $items)
                <tr>
                    <td colspan="16" class="fw-bold bg-light">{{ $group }}</td>
                </tr>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $item->subSubKelompok()->first()->sub_sub_kelompok ?? '-' }}</td>
                        <td>{{ $item->merek }}</td>
                        <td>{{ $item->no_seri_pabrik ?? '-' }}</td>
                        <td>{{ $item->spesifikasi ?? '-' }}</td>
                        <td>{{ $item->bahan }}</td>
                        <td class="text-center">{{ $item->tahun_pembelian }}</td>
                        <td>{{ $item->kode_lokasi ?: '-' }}</td>
                        <td>{{ $item->id }}</td>
                        <td class="text-center">{{ $item->jumlah ?? 1 }}</td>
                        <td class="text-end">{{ number_format($item->harga,0,',','.') }}</td>
                        <td class="text-center">{{ $item->kondisi == 'Baik' ? '1' : '' }}</td>
                        <td class="text-center">{{ $item->kondisi == 'Kurang Baik' ? '1' : '' }}</td>
                        <td class="text-center">{{ $item->kondisi == 'Rusak Berat' ? '1' : '' }}</td>
                        <td>{{ $item->keterangan }}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
        <div class="text-end me-2">(QR-AST.PA/01-02)</div>
        
        @if($request->filled('gabungkan_aset') && $request->filled('kolom_gabungan') && false)
            <!-- Grouping Information -->
            <div class="mt-4">
                <h6 class="fw-bold">Informasi Penggabungan Aset:</h6>
                <p class="mb-2">
                    <strong>Aset telah digabungkan berdasarkan kolom:</strong> 
                    {{ implode(', ', array_map(function($col) {
                        $labels = [
                            'merek' => 'Merk/Model',
                            'no_seri_pabrik' => 'No Seri Pabrik',
                            'spesifikasi' => 'Ukuran',
                            'bahan' => 'Bahan',
                            'tahun' => 'Tahun Pembelian'
                        ];
                        return $labels[$col] ?? $col;
                    }, $request->kolom_gabungan)) }}
                </p>
                
                @php
                    $totalGrouped = 0;
                    $totalKeterangan = 0;
                    foreach($data as $group => $items) {
                        foreach($items as $item) {
                            if (isset($item->grouping_info)) {
                                $totalGrouped += $item->grouping_info['total_items'];
                                $totalKeterangan += $item->grouping_info['keterangan_count'];
                            }
                        }
                    }
                @endphp
                
                <p class="mb-2">
                    <strong>Total aset yang digabungkan:</strong> {{ $totalGrouped }} item<br>
                    <strong>Total keterangan unik:</strong> {{ $totalKeterangan }} item
                </p>
            </div>
        @endif
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="row">
                <div class="col-4 text-center">
                    <div class="fw-bold">Mengetahui</div>
                    <div class="mb-2">Manajer Aset</div>
                    <div style="height: 60px;"></div>
                    <div>(ARY ISWAHYUDI, S.SOS)</div>
                </div>
                <div class="col-4 text-center">
                    <div class="fw-bold">Penanggung Jawab</div>
                    <div class="mb-2">&nbsp;</div>
                    <div style="height: 60px;"></div>
                    <div>({{ $penanggungJawab ? $penanggungJawab->nama : '................................................' }})</div>
                </div>
                <div class="col-4 text-center">
                    <div class="fw-bold">Dibuat Oleh</div>
                    <div class="mb-2">As.Man.Pendayagunaan Aset</div>
                    <div style="height: 60px;"></div>
                    <div>(ERIKA DEVAYANTHY, SE)</div>
                </div>
            </div>
        </div>
    @endif
</div>
</body>
</html> 