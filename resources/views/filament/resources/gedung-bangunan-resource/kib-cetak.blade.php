@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Inventaris Barang (KIB) C - Gedung & Bangunan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; }
        .table th, .table td { vertical-align: middle !important; }
        .footer-table td, .footer-table th { padding: 0 !important; font-size: 11px; border: none !important; }
        .signature-section { margin-top: 50px; }
        .signature-line { border-bottom: 1px dotted #000; min-width: 200px; display: inline-block; }
        .admin-info { margin-bottom: 20px; }
        .admin-info .form-group { margin-bottom: 10px; }
        .admin-info label { font-weight: bold; margin-right: 10px; }
        .admin-info .dotted-line { border-bottom: 1px dotted #000; min-width: 200px; display: inline-block; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="text-center my-3">
        <img src="/images/logo.png" alt="Logo" style="height:120px;">
        <h5 class="fw-bold mt-2">KARTU INVESTASI BARANG (KIB)</h5>
        <h6 class="fw-bold">C. BANGUNAN DAN GEDUNG</h6>
    </div>
    
    <table class="table table-borderless footer-table" style="width:auto">
        @isset($wilayah)
        <tr>
            <td>WILAYAH</td>
            <td class="text-center" width="10px">:</td>
            <td class="text-uppercase">{{ $wilayah->wilayah ?? '................................................' }}</td>
        </tr>
        @endisset
        <tr>
            <td>DIREKTORAT</td>
            <td class="text-center" width="10px">:</td>
            <td class="text-uppercase">{{ $direktorat->direktorat ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>BIDANG</td>
            <td class="text-center" width="10px">:</td>
            <td class="text-uppercase">{{ $bidang->bidang ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>SUB BIDANG</td>
            <td class="text-center" width="10px">:</td>
            <td class="text-uppercase">{{ $subBidang->sub_bidang ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>LOKASI UNIT KERJA</td>
            <td class="text-center" width="10px">:</td>
            <td class="text-uppercase">{{ $unit->unit ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>KODE LOKASI</td>
            <td class="text-center" width="10px">:</td>
            <td class="text-uppercase">{{ $kodeLokasi ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>KIB</td>
            <td class="text-center" width="10px">:</td>
            <td>C</td>
        </tr>
    </table>

    <!-- Main Table -->
    <table class="table table-sm align-middle table-bordered mb-1">
        <thead class="table-light text-center">
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Jenis Barang/<br>Nama Barang</th>
            <th colspan="2">Nomor</th>
            <th colspan="3">Kondisi</th>
            <th colspan="2">Konstruksi Bangunan</th>
            <th rowspan="2">Luas Lantai<br>(m²)</th>
            <th rowspan="2">Letak/<br>Alamat</th>
            <th rowspan="2">Tahun<br>Pengadaan</th>
            <th colspan="4">Tanah Bangunan</th>
            <th rowspan="2">Harga<br>(Rp.)</th>
            <th rowspan="2">PIC</th>
            <th rowspan="2">Dokumentasi</th>
            <th rowspan="2">Keterangan</th>
        </tr>
        <tr class="text-center">
            <th>Kode Barang</th>
            <th>Register</th>
            <th>B</th>
            <th>KB</th>
            <th>RB</th>
            <th>Bertingkat/<br>Tidak</th>
            <th>Beton/<br>Tidak</th>
            <th>Luas Tanah<br>(m²)</th>
            <th>Status Tanah</th>
            <th>Nomor Sertifikat<br>Tanah</th>
            <th>Asal-usul<br>Tanah</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $i => $item)
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $item->subSubKelompok()->value('sub_sub_kelompok') ?? '-' }}</td>
                <td>{{ $item->subSubKelompok()->value('id') ?? '-' }}</td>
                <td>{{ substr($item->id ?? '-', -4) }}</td>
                <td class="text-center">{{ $item->kondisi == 'Baik' ? '✓' : '' }}</td>
                <td class="text-center">{{ $item->kondisi == 'Kurang Baik' ? '✓' : '' }}</td>
                <td class="text-center">{{ $item->kondisi == 'Rusak Berat' ? '✓' : '' }}</td>
                <td class="text-center">{{ $item->bertingkat ? 'Bertingkat' : 'Tidak' }}</td>
                <td class="text-center">{{ $item->beton ? 'Beton' : 'Tidak' }}</td>
                <td class="text-center">{{ $item->luas_lantai ?? '-' }}</td>
                <td>{{ $item->letak ?? '-' }}</td>
                <td class="text-center">{{ $item->tanggal_pengadaan ? Carbon::parse($item->tanggal_pengadaan)->format('Y') : ($item->tahun ?? '-') }}</td>
                <td class="text-center">{{ $item->luas_tanah ?? '-' }}</td>
                <td class="text-center">{{ $item->status_tanah ?? '-' }}</td>
                <td>{{ $item->nomor_sertifikat ?? '-' }}</td>
                <td class="text-center">{{ $item->asal_usul ?? '-' }}</td>
                <td class="text-end">{{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
                <td>{{ $item->subBidang?->sub_bidang ?? '-' }}</td>
                <td class="text-center">{{ $item->hasDokumentasi() ? 'Ada' : 'Tidak Ada' }}</td>
                <td>{{ $item->keterangan ?? '-' }}</td>
            </tr>
        @endforeach
        <tr class="fw-bold text-end bg-light d-print-none">
            <td colspan="16">Total</td>
            <td class="text-end">{{ number_format($subtotal ?? 0, 0, ',', '.') }}</td>
            <td colspan="3"></td>
        </tr>
        </tbody>
    </table>
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