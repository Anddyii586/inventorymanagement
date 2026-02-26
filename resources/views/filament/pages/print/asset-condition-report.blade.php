@php
    use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kondisi Aset</title>
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
        <img src="/images/logo.png" alt="Logo" style="height:100px;">
        <h5 class="fw-bold mt-2">LAPORAN KONDISI ASET</h5>
        <div class="subtitle">Kondisi: {{ $kondisi }}</div>
        <div class="subtitle">Dicetak Tanggal: {{ Carbon::now()->isoFormat('D MMMM Y') }}</div>
    </div>

    <!-- Main Table -->
    <table class="table table-bordered mb-1" style="font-size: 11px;">
        <thead>
        <tr class="table-light">
            <th class="text-center" style="width: 40px;">No</th>
            <th class="text-center">Kode Lokasi</th>
            <th class="text-center">Nama Aset</th>
            <th class="text-center">Merek / Tipe</th>
            <th class="text-center">Kondisi</th>
            <th class="text-center">Unit / Lokasi</th>
            <th class="text-center">Nilai Aset</th>
        </tr>
        </thead>
        <tbody>
        @forelse($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->kode_lokasi ?? '-' }}</td>
                <td>{{ $item->nama_barang ?? '-' }}</td>
                <td>{{ $item->merek ?? '-' }} {{ $item->tipe ? '/ '.$item->tipe : '' }}</td>
                <td class="text-center">
                    @if($item->kondisi == 'Baik')
                        <span class="badge bg-success text-white">Baik</span>
                    @elseif($item->kondisi == 'Kurang Baik')
                         <span class="badge bg-warning text-dark">Kurang Baik</span>
                    @elseif($item->kondisi == 'Rusak Berat')
                         <span class="badge bg-danger text-white">Rusak Berat</span>
                    @else
                        {{ $item->kondisi ?? '-' }}
                    @endif
                </td>
                <td>
                    {{ $item->unit->unit ?? '-' }}<br>
                    <small class="text-muted">{{ $item->ruangan->nama ?? '' }}</small>
                </td>
                <td class="text-end">{{ $item->harga ? 'Rp ' . number_format($item->harga, 0, ',', '.') : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-3">Tidak ada data aset ditemukan</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold bg-light">
                <td colspan="6" class="text-end">TOTAL NILAI ASET</td>
                <td class="text-end">{{ 'Rp ' . number_format($data->sum('harga'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="text-end me-2 mb-4" style="font-size: 10px;">Dicetak oleh: {{ auth()->user()->nama ?? 'Sistem' }}</div>
    
    <!-- Signature Section -->
    <div class="signature-section d-print-none">
        <button onclick="window.print()" class="btn btn-primary d-print-none">Cetak Laporan</button>
    </div>
</div>
</body>
</html>
