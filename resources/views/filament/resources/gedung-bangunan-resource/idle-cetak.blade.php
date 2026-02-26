@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Bangunan dan Gedung Idle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; }
        .table th, .table td { vertical-align: middle !important; padding: 4px !important; }
        .table { border-collapse: collapse; }
        .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
        .footer-table td, .footer-table th { padding: 0 !important; font-size: 11px; border: none !important; }
        .signature-section { margin-top: 50px; }
        .signature-line { border-bottom: 1px dotted #000; min-width: 200px; display: inline-block; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; font-weight: bold; }
        .header-info td { padding: 2px 8px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <!-- Header dengan Logo -->
    <div class="text-center my-3">
        <img src="/images/logo.png" alt="Logo" style="height:80px;">
        <div class="title mt-2">PT AIR MINUM GIRI MENANG (PERSERODA)</div>
        <div class="title mt-1">DAFTAR BANGUNAN DAN GEDUNG IDLE</div>
    </div>

    <!-- Info Header -->
    <table class="table table-borderless header-info mb-3">
        <tr>
            <td width="150px">DIREKTORAT</td>
            <td>:</td>
            <td>{{ $direktorat ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>BIDANG</td>
            <td>:</td>
            <td>{{ $bidang ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>SUB BIDANG</td>
            <td>:</td>
            <td>{{ $subBidang ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>LOKAL UNIT KERJA</td>
            <td>:</td>
            <td>{{ $lokasiUnitKerja ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>KODE LOKASI</td>
            <td>:</td>
            <td>{{ $kodeLokasi ?? '................................................' }}</td>
        </tr>
        <tr>
            <td>KIB</td>
            <td>:</td>
            <td>A / B / C / D / E</td>
        </tr>
    </table>

    <table class="table table-bordered mb-1" style="font-size: 10px;">
        <thead>
        <tr>
            <th rowspan="2" style="width: 30px; text-align: center;">NO</th>
            <th rowspan="2" style="width: 120px; text-align: center;">JENIS BARANG/<br>NAMA BARANG</th>
            <th colspan="2" style="width: 100px; text-align: center;">NOMOR</th>
            <th colspan="3" style="width: 90px; text-align: center;">KONDISI</th>
            <th colspan="2" style="width: 100px; text-align: center;">KONSTRUKSI<br>BANGUNAN</th>
            <th rowspan="2" style="width: 60px; text-align: center;">LUAS LANTAI<br>(M2)</th>
            <th rowspan="2" style="width: 100px; text-align: center;">LETAK/<br>ALAMAT</th>
            <th rowspan="2" style="width: 60px; text-align: center;">TAHUN<br>PENGADAAN</th>
            <th colspan="3" style="width: 150px; text-align: center;">TAHUN BANGUNAN</th>
            <th rowspan="2" style="width: 80px; text-align: center;">HARGA<br>(RP.)</th>
            <th rowspan="2" style="width: 60px; text-align: center;">PIC</th>
            <th rowspan="2" style="width: 60px; text-align: center;">DOKUMENTASI</th>
            <th rowspan="2" style="width: 80px; text-align: center;">KETERANGAN</th>
        </tr>
        <tr>
            <th style="width: 50px; text-align: center;">KODE<br>BARANG</th>
            <th style="width: 50px; text-align: center;">REGISTER</th>
            <th style="width: 30px; text-align: center;">B</th>
            <th style="width: 30px; text-align: center;">KB</th>
            <th style="width: 30px; text-align: center;">RB</th>
            <th style="width: 50px; text-align: center;">BERTINGKAT/<br>TIDAK</th>
            <th style="width: 50px; text-align: center;">BETON/<br>TIDAK</th>
            <th style="width: 50px; text-align: center;">SISTEM<br>TANAH</th>
            <th style="width: 50px; text-align: center;">NOMOR<br>SERTIFIKAT TANAH</th>
            <th style="width: 50px; text-align: center;">ASAL-USUL<br>TANAH</th>
        </tr>
        <tr style="font-size: 9px; text-align: center; font-style: italic;">
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
            <td>19</td>
        </tr>
        </thead>
        <tbody>
        @if($data->count() > 0)
            @foreach($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->subSubKelompok()->first()->sub_sub_kelompok ?? 'Gedung/Bangunan' }}</td>
                    <td class="text-center">{{ $item->subSubKelompok()->first()->id ?? '-' }}</td>
                    <td class="text-center">{{ substr($item->id ?? '-', -4) }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Kurang Baik' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->kondisi == 'Rusak Berat' ? '✓' : '' }}</td>
                    <td class="text-center">{{ $item->bertingkat ? 'Bertingkat' : 'Tidak' }}</td>
                    <td class="text-center">{{ $item->beton ? 'Beton' : 'Tidak' }}</td>
                    <td class="text-center">{{ $item->luas_lantai ?? '-' }}</td>
                    <td>{{ $item->letak ?? '-' }}</td>
                    <td class="text-center">{{ $item->tahun ?? ($item->tanggal_pengadaan ? \Carbon\Carbon::parse($item->tanggal_pengadaan)->format('Y') : '-') }}</td>
                    <td class="text-center">{{ $item->status_tanah ?? '-' }}</td>
                    <td>{{ $item->nomor_sertifikat ?? '-' }}</td>
                    <td>{{ $item->asal_usul ?? '-' }}</td>
                    <td class="text-end">{{ $item->harga ? number_format($item->harga, 0, ',', '.') : '-' }}</td>
                    <td>{{ $item->user ?? '-' }}</td>
                    <td>{{ $item->hasDokumentasi() ? 'Ada' : '-' }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
            <tr class="fw-bold text-end bg-light d-print-none">
                <td colspan="16" class="text-center">JUMLAH</td>
                <td class="text-end">{{ number_format($total ?? 0, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        @else
            <tr>
                <td colspan="20" class="text-center">Tidak ada data bangunan dan gedung idle</td>
            </tr>
        @endif
        </tbody>
    </table>
    
    <div class="text-end me-2">(QR-AST.PA/01-08)</div>
    
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
                <div class="mb-2">As. Man. Pendayagunaan Aset</div>
                <div style="height: 60px;"></div>
                <div>(ERIKA DEVAYANTHY, SE)</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

