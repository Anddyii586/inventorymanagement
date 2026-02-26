@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Inventaris Barang (KIB) - Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; }
        .table th, .table td { vertical-align: middle !important; }
        .footer-table td, .footer-table th { padding: 0 !important; font-size: 11px; border: none !important; }
        .signature-section { margin-top: 50px; }
        .signature-line { border-bottom: 1px dotted #000; min-width: 200px; display: inline-block; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; font-weight: bold; }
        .header-info td { padding: 2px 8px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <!-- Header dengan Logo -->
    <div class="text-center my-3">
        <img src="/images/logo.png" alt="Logo" style="height:80px;">
        <div class="title mt-2">KARTU INVENTARIS BARANG (KIB)</div>
        <div class="subtitle">A. TANAH</div>
    </div>

    <!-- Info Header -->
    <table class="table table-borderless header-info mb-3">
        <tr>
            <td width="150px">DIREKTORAT</td>
            <td>:</td>
            <td>................................................</td>
        </tr>
        <tr>
            <td>BIDANG</td>
            <td>:</td>
            <td>................................................</td>
        </tr>
        <tr>
            <td>SUB BIDANG</td>
            <td>:</td>
            <td>................................................</td>
        </tr>
        <tr>
            <td>LOKASI UNIT KERJA</td>
            <td>:</td>
            <td>................................................</td>
        </tr>
        <tr>
            <td>KODE LOKASI</td>
            <td>:</td>
            <td>{{ $kodeLokasi }}</td>
        </tr>
        <tr>
            <td>KIB</td>
            <td>:</td>
            <td>A / B / C / D / E / F</td>
        </tr>
    </table>

    <table class="table table-bordered mb-1">
        <thead>
        <tr>
            <th rowspan="2" style="width: 30px;">NO</th>
            <th rowspan="2" style="width: 120px;">NAMA BARANG/JENIS BARANG</th>
            <th rowspan="2" style="width: 80px;">KODE BARANG</th>
            <th rowspan="2" style="width: 60px;">REGISTER</th>
            <th rowspan="2" style="width: 60px;">LUAS (M2)</th>
            <th rowspan="2" style="width: 60px;">TAHUN PENGADAAN</th>
            <th rowspan="2" style="width: 100px;">LETAK/ALAMAT</th>
            <th rowspan="2" style="width: 60px;">HAK</th>
            <th colspan="2" style="width: 120px;">STATUS TANAH</th>
            <th rowspan="2" style="width: 80px;">PENGGUNAAN</th>
            <th rowspan="2" style="width: 80px;">ASAL USUL PEROLEHAN</th>
            <th rowspan="2" style="width: 80px;">MASA BERLAKU (TAHUN)</th>
            <th rowspan="2" style="width: 80px;">HARGA PEMBELIAN (RP)</th>
            <th rowspan="2" style="width: 60px;">KONDISI</th>
            <th rowspan="2" style="width: 60px;">DOKUMENTASI</th>
            <th rowspan="2" style="width: 60px;">PIC</th>
            <th rowspan="2" style="width: 80px;">KETERANGAN</th>
        </tr>
        <tr>
            <th style="width: 60px;">SERTIFIKAT TANGGAL</th>
            <th style="width: 60px;">NOMOR</th>
        </tr>
        <tr style="font-size: 9px; text-align: center;">
            <td>1</td>
            <td>2</td>
            <td>3</td>
            <td>4</td>
            <td>5</td>
            <td>6</td>
            <td>7</td>
            <td>8</td>
            <td>9</td>
            <td>10</td>
            <td>11</td>
            <td>12</td>
            <td>13</td>
            <td>14</td>
            <td>15</td>
            <td>16</td>
            <td>17</td>
            <td>18</td>
        </tr>
        </thead>
        <tbody>
        @foreach($grouped as $group => $data)
            @if($data->count())
                <tr>
                    <td colspan="18" class="fw-bold bg-light">{{ $group }}</td>
                </tr>
                @foreach($data as $i => $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->subSubKelompok()->first()->sub_sub_kelompok ?? 'Tanah' }}</td>
                        <td>{{ $item->subSubKelompok()->first()->id }}</td>
                        <td>{{ substr($item->id ?? '-', -4) }}</td>
                        <td class="text-center">{{ $item->luas ?? '-' }}</td>
                        <td class="text-center">{{ $item->tahun ?? \Carbon\Carbon::parse($item->tanggal_pengadaan)->format('Y') ?? '-' }}</td>
                        <td>{{ $item->letak ?? '-' }}</td>
                        <td>{{ $item->hak ?? '-' }}</td>
                        <td class="text-center">{{ $item->tanggal_sertifikat ? \Carbon\Carbon::parse($item->tanggal_sertifikat)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $item->nomor_sertifikat ?? '-' }}</td>
                        <td>{{ $item->penggunaan ?? '-' }}</td>
                        <td>{{ $item->asal_usul ?? '-' }}</td>
                        <td>{{ $item->jangka_waktu ?? '-' }}</td>
                        <td class="text-end">{{ $item->harga ? number_format($item->harga,0,',','.') : '-' }}</td>
                        <td>{{ $item->kondisi ?? '-' }}</td>
                        <td>{{ $item->dokumentasi ? 'Ada' : '-' }}</td>
                        <td>{{ $item->user ?? '-' }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold text-end bg-light d-print-none">
                    <td colspan="13">
                        Subtotal 
                    </td>
                    <td class="text-end">
                        {{ number_format($subtotals[$group] ?? 0, 0, ',', '.') }}
                    </td>
                    <td colspan="4"></td>
                </tr>
            @endif
        @endforeach
        <tr class="fw-bold text-end bg-secondary text-white d-print-none">
            <td colspan="13">
                Total
            </td>
            <td class="text-end">
                {{ number_format($total ?? 0, 0, ',', '.') }}
            </td>
            <td colspan="4"></td>
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