@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Inventaris Barang (KIB)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; }
        .table th, .table td { vertical-align: middle !important; }
        .footer-table td, .footer-table th { padding: 0 !important; font-size: 11px; border: none !important; }
        .signature-section { margin-top: 50px; }
        .signature-line { border-bottom: 1px dotted #000; min-width: 200px; display: inline-block; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="text-center my-3">
        <img src="/images/logo.png" alt="Logo" style="height:120px;">
        <h5 class="fw-bold mt-2">KARTU INVENTARIS BARANG (KIB)</h5>
        <h6 class="fw-bold">B. PERALATAN DAN MESIN</h6>
        @if($kategoriKib == 'Kendaraan Dinas')
            <h6 class="fw-bold">1. KENDARAAN DINAS</h6>
        @elseif($kategoriKib == 'Peralatan')
            <h6 class="fw-bold">2. PERALATAN DAN MESIN</h6>
        @elseif($kategoriKib == 'Pompa')
            <h6 class="fw-bold">3. POMPA</h6>
        @endif
    </div>
    <div class="mb-2 fw-bold">Kode Lokasi : {{ request()->kode_lokasi ?? '-' }}</div>
    @if($kategoriKib == 'Kendaraan Dinas')
        <!-- Tabel untuk Kendaraan Dinas (21 kolom) -->
        <table class="table table-sm align-middle table-bordered mb-1">
            <thead class="table-light text-center">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Kode Barang</th>
                <th rowspan="2">Jenis Barang/<br>Nama Barang</th>
                <th rowspan="2">Nomor Register</th>
                <th rowspan="2">Merk/Type</th>
                <th rowspan="2">Ukuran/CC</th>
                <th rowspan="2">Bahan</th>
                <th rowspan="2">Tahun Pembelian</th>
                <th colspan="5">Nomor</th>
                <th rowspan="2">Asal-usul Perolehan</th>
                <th rowspan="2">Harga (Rp)</th>
                <th colspan="3">Kondisi Barang</th>
                <th rowspan="2">PIC</th>
                <th rowspan="2">Dokumentasi</th>
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr class="text-center">
                <th>Pabrik</th>
                <th>Rangka</th>
                <th>Mesin</th>
                <th>Polisi</th>
                <th>BPKB</th>
                <th>B</th>
                <th>KB</th>
                <th>RB</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $item->subSubKelompok()->value('id') ?? '-' }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ substr($item->id ?? '-', -4) }}</td>
                    <td>{{ $item->merek }}</td>
                    <td>{{ $item->spesifikasi ?? '-' }}</td>
                    <td>{{ $item->bahan }}</td>
                    <td>{{ $item->tahun_pembelian ?? $item->tahun }}</td>
                    <td>{{ $item->nomor_pabrik ?? '-' }}</td>
                    <td>{{ $item->nomor_rangka ?? '-' }}</td>
                    <td>{{ $item->nomor_mesin ?? '-' }}</td>
                    <td>{{ $item->nomor_polisi ?? '-' }}</td>
                    <td>{{ $item->bpkb ?? '-' }}</td>
                    <td>{{ $item->asal_usul }}</td>
                    <td class="text-end">{{ number_format($item->harga,0,',','.') }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Kurang Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Rusak Berat' ? '✓' : '' }}</td>
                    <td>{{ $item->subBidang->sub_bidang ?? '-' }}</td>
                    <td>{{ $item->dokumentasi ? 'Ada' : 'Tidak Ada' }}</td>
                    <td>{{ $item->keterangan }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold text-end bg-light d-print-none">
                <td colspan="14">Total</td>
                <td class="text-end">{{ number_format($subtotal ?? 0, 0, ',', '.') }}</td>
                <td colspan="6"></td>
            </tr>
            </tbody>
        </table>
    @elseif($kategoriKib == 'Pompa')
        <!-- Tabel untuk Pompa (31 kolom) -->
        <table class="table table-sm align-middle table-bordered mb-1">
            <thead class="table-light text-center">
            <tr>
                <th rowspan="3">No</th>
                <th rowspan="3">Kode Barang</th>
                <th rowspan="3">Jenis Barang/<br>Nama Barang</th>
                <th rowspan="3">Nomor Register</th>
                <th rowspan="3">Merk</th>
                <th rowspan="3">Type</th>
                <th rowspan="3">Bahan</th>
                <th colspan="3">Spesifikasi Pompa</th>
                <th colspan="4">Panel Pompa</th>
                <th colspan="8">Kelistrikan</th>
                <th rowspan="3">Tahun Perolehan</th>
                <th rowspan="3">Asal-usul Perolehan</th>
                <th rowspan="3">Harga (Rp)</th>
                <th colspan="3" rowspan="2">Kondisi Pompa</th>
                <th rowspan="3">PIC</th>
                <th rowspan="3">Dokumentasi</th>
                <th rowspan="3">Keterangan</th>
            </tr>
            <tr class="text-center">
                <th rowspan="2">Kapasitas Listrik (KWH)</th>
                <th rowspan="2">Kapasitas Air</th>
                <th rowspan="2">Head (Tekanan)</th>
                <th rowspan="2">Merk Panel Pompa</th>
                <th rowspan="2">Type Panel Pompa</th>
                <th colspan="2">RTU</th>
                <th rowspan="2">Kapasitas Listrik (VA)</th>
                <th rowspan="2">SLO</th>
                <th rowspan="2">JIL</th>
                <th rowspan="2">Genset</th>
                <th colspan="2">Panel Listrik</th>
                <th colspan="2">Rumah Panel</th>
            </tr>
            <tr>
                <th>Ada</th>
                <th>Tidak</th>
                <th>Ada</th>
                <th>Tidak</th>
                <th>Ada</th>
                <th>Tidak</th>
                <th>B</th>
                <th>KB</th>
                <th>RB</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $item->subSubKelompok()->value('id') ?? '-' }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ substr($item->id ?? '-', -4) }}</td>
                    <td>{{ $item->merek }}</td>
                    <td>{{ $item->tipe }}</td>
                    <td>{{ $item->bahan }}</td>
                    <td>{{ $item->kapasitas_listrik_kwh ?? '-' }}</td>
                    <td>{{ $item->kapasitas_air ?? '-' }}</td>
                    <td>{{ $item->head_tekanan ?? '-' }}</td>
                    <td>{{ $item->merk_panel_pompa ?? '-' }}</td>
                    <td>{{ $item->type_panel_pompa ?? '-' }}</td>
                    <td class="text-center">{{ $item->rtu ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->rtu ? '' : '✓' }}</td>
                    <td>{{ $item->kapasitas_listrik_va ?? '-' }}</td>
                    <td>{{ $item->slo ?? '-' }}</td>
                    <td>{{ $item->jil ?? '-' }}</td>
                    <td>{{ $item->genset ?? '-' }}</td>
                    <td class="text-center">{{ $item->panel_listrik ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->panel_listrik ? '' : '✓' }}</td>
                    <td class="text-center">{{ $item->rumah_panel ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->rumah_panel ? '' : '✓' }}</td>
                    <td>{{ $item->tahun_pembelian ?? $item->tahun }}</td>
                    <td>{{ $item->asal_usul }}</td>
                    <td class="text-end">{{ number_format($item->harga,0,',','.') }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Kurang Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Rusak Berat' ? '✓' : '' }}</td>
                    <td>{{ $item->subBidang->sub_bidang ?? '-' }}</td>
                    <td>{{ $item->dokumentasi ? 'Ada' : 'Tidak Ada' }}</td>
                    <td>{{ $item->keterangan }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold text-end bg-light d-print-none">
                <td colspan="24">Total</td>
                <td class="text-end">{{ number_format($subtotal ?? 0, 0, ',', '.') }}</td>
                <td colspan="6"></td>
            </tr>
            </tbody>
        </table>
    @else
        <!-- Tabel untuk Peralatan (16 kolom) -->
        <table class="table table-sm align-middle table-bordered mb-1">
            <thead class="table-light text-center">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Kode Barang</th>
                <th rowspan="2">Jenis Barang/<br>Nama Barang</th>
                <th rowspan="2">Nomor Register</th>
                <th rowspan="2">Merk/Type</th>
                <th rowspan="2">Ukuran/CC</th>
                <th rowspan="2">Bahan</th>
                <th rowspan="2">Tahun Pembelian</th>
                <th rowspan="2">Asal-usul Cara Perolehan</th>
                <th rowspan="2">Harga (Rp)</th>
                <th colspan="3">Kondisi Barang</th>
                <th rowspan="2">PIC</th>
                <th rowspan="2">Dokumentasi</th>
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr class="text-center">
                <th>B</th>
                <th>KB</th>
                <th>RB</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $item->subSubKelompok()->value('id') ?? '-' }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ substr($item->id ?? '-', -4) }}</td>
                    <td>{{ $item->merek }}</td>
                    <td>{{ $item->spesifikasi ?? '-' }}</td>
                    <td>{{ $item->bahan }}</td>
                    <td>{{ $item->tahun_pembelian ?? $item->tahun }}</td>
                    <td>{{ $item->asal_usul }}</td>
                    <td class="text-end">{{ number_format($item->harga,0,',','.') }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Kurang Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Rusak Berat' ? '✓' : '' }}</td>
                    <td>{{ $item->subBidang->sub_bidang ?? '-' }}</td>
                    <td>{{ $item->dokumentasi ? 'Ada' : 'Tidak Ada' }}</td>
                    <td>{{ $item->keterangan }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold text-end bg-light d-print-none">
                <td colspan="9">Total</td>
                <td class="text-end">{{ number_format($subtotal ?? 0, 0, ',', '.') }}</td>
                <td colspan="6"></td>
            </tr>
            </tbody>
        </table>
    @endif
    <div class="text-end me-2">(QR-AST.PA/01-01)</div>
    
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
</div>
</body>
</html> 