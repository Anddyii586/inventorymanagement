@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Format Barang Dalam Kondisi Rusak Berat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; }
        .table th, .table td { vertical-align: middle !important; padding: 4px !important; }
        .header-info td { padding: 2px 8px; }
        .signature-section { margin-top: 50px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; font-weight: bold; }
        @media print {
            .d-print-none { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="text-center my-3">
        <img src="/images/logo.png" alt="Logo" style="height:120px;">
        <h5 class="fw-bold mt-2">BARANG DALAM KONDISI RUSAK BERAT</h5>
    </div>
    <table class="table table-borderless header-info" style="width:auto">
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
            <td>B</td>
        </tr>
    </table>

    <!-- Main Table -->
    <table class="table table-bordered mb-1" style="font-size: 11px;">
        <thead>
        <tr>
            <th rowspan="2" style="width: 30px; text-align: center;">NO</th>
            <th rowspan="2" style="width: 100px; text-align: center;">KODE BARANG</th>
            <th rowspan="2" style="width: 150px; text-align: center;">NAMA/JENIS BARANG</th>
            <th rowspan="2" style="width: 120px; text-align: center;">MERK/TYPE/ALAMAT</th>
            <th rowspan="2" style="width: 100px; text-align: center;">ASAL/CARA PEROLEHAN</th>
            <th rowspan="2" style="width: 80px; text-align: center;">TAHUN PEROLEHAN</th>
            <th colspan="3" style="text-align: center;">JUMLAH</th>
            <th rowspan="2" style="width: 100px; text-align: center;">KETERANGAN</th>
        </tr>
        <tr>
            <th style="width: 60px; text-align: center;">BARANG</th>
            <th style="width: 100px; text-align: center;">HARGA</th>
            <th style="width: 100px; text-align: center;">NILAI BUKU</th>
        </tr>
        </thead>
        <tbody>
        @php
            $globalCounter = 1;
        @endphp
        @foreach($grouped as $group => $data)
            @if($data->count())
                <tr>
                    <td colspan="10" class="fw-bold bg-light">{{ $group }}</td>
                </tr>
                @foreach($data as $item)
                    <tr>
                        <td class="text-center">{{ $globalCounter++ }}</td>
                        <td>{{ $item->subSubKelompok()->first()->id ?? '-' }}</td>
                        <td>{{ $item->nama_barang ?? '-' }}</td>
                        <td>
                            @if($item->kategori === 'Kendaraan Dinas')
                                {{ $item->merek ?? '-' }} / {{ $item->tipe ?? '-' }}
                            @else
                                {{ $item->merek ?? '-' }} / {{ $item->tipe ?? '-' }} / {{ $item->spesifikasi ?? '-' }}
                            @endif
                        </td>
                        <td>{{ $item->asal_usul ?? '-' }}</td>
                        <td class="text-center">{{ $item->tahun_pembelian ?? ($item->tahun ?? '-') }}</td>
                        <td class="text-center">1</td>
                        <td class="text-end">{{ $item->harga ? number_format($item->harga, 0, ',', '.') : '-' }}</td>
                        <td class="text-end">{{ $item->harga ? number_format($item->harga, 0, ',', '.') : '-' }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold text-end bg-light d-print-none">
                    <td colspan="6" class="text-end">Jumlah</td>
                    <td class="text-center">{{ $data->count() }}</td>
                    <td class="text-end">{{ number_format($subtotals[$group] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($subtotals[$group] ?? 0, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        @endforeach
        <tr class="fw-bold text-end bg-secondary text-white d-print-none">
            <td colspan="6" class="text-end">TOTAL</td>
            <td class="text-center">{{ collect($grouped)->flatten()->count() }}</td>
            <td class="text-end">{{ number_format($total ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($total ?? 0, 0, ',', '.') }}</td>
            <td></td>
        </tr>
        </tbody>
    </table>
    
    <div class="text-end me-2">(QR-AST.PA/01-12)</div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <div class="row">
            <div class="col-6 text-center">
                <div class="fw-bold">Mengetahui</div>
                <div class="mb-2">Manajer Aset</div>
                <div style="height: 60px;"></div>
                <div>(ARY ISWAHYUDI, S.SOS)</div>
            </div>
            <div class="col-6 text-center">
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

