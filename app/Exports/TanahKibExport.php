<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TanahKibExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $type;

    public function __construct($data, $type = 'kib')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        if ($this->type === 'idle') {
            return [
                'No',
                'Nama Barang/Jenis Barang',
                'Kode Barang',
                'Register',
                'Luas (M2)',
                'Tahun Pengadaan',
                'Letak/Alamat',
                'Hak',
                'Tanggal Sertifikat',
                'Nomor Sertifikat',
                'Penggunaan',
                'Asal-usul Perolehan',
                'Jangka Waktu',
                'Tahun Berakhir',
                'Harga Pembelian (Rp.)',
                'Kondisi',
                'PIC',
                'Dokumentasi',
                'Keterangan',
            ];
        }
        
        return [
            'No',
            'Nama Barang/Jenis Barang',
            'Kode Barang',
            'Register',
            'Luas (M2)',
            'Tahun Pengadaan',
            'Letak/Alamat',
            'Hak',
            'Sertifikat Tanggal',
            'Sertifikat Nomor',
            'Penggunaan',
            'Asal Usul Perolehan',
            'Masa Berlaku (Tahun)',
            'Harga Pembelian (Rp.)',
            'Kondisi',
            'Dokumentasi',
            'PIC',
            'Keterangan',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;
        
        $subSubKelompok = $item->subSubKelompok()->first();
        $tahun = $item->tahun ?? ($item->tanggal_pengadaan ? \Carbon\Carbon::parse($item->tanggal_pengadaan)->format('Y') : '-');
        $tanggalSertifikat = $item->tanggal_sertifikat ? \Carbon\Carbon::parse($item->tanggal_sertifikat)->format('d/m/Y') : '-';
        $harga = $item->harga ? number_format($item->harga, 0, ',', '.') : '-';
        $dokumentasi = $item->hasDokumentasi() ? 'Ada' : '-';
        
        if ($this->type === 'idle') {
            return [
                $no,
                $subSubKelompok->sub_sub_kelompok ?? 'Tanah',
                $subSubKelompok->id ?? '-',
                substr($item->id ?? '-', -4),
                $item->luas ?? '-',
                $tahun,
                $item->letak ?? '-',
                $item->hak ?? '-',
                $tanggalSertifikat,
                $item->nomor_sertifikat ?? '-',
                $item->penggunaan ?? '-',
                $item->asal_usul ?? '-',
                $item->jangka_waktu ?? '-',
                $item->berakhir ?? '-',
                $harga,
                $item->kondisi ?? '-',
                $item->user ?? '-',
                $dokumentasi,
                $item->keterangan ?? '-',
            ];
        }
        
        return [
            $no,
            $subSubKelompok->sub_sub_kelompok ?? 'Tanah',
            $subSubKelompok->id ?? '-',
            substr($item->id ?? '-', -4),
            $item->luas ?? '-',
            $tahun,
            $item->letak ?? '-',
            $item->hak ?? '-',
            $tanggalSertifikat,
            $item->nomor_sertifikat ?? '-',
            $item->penggunaan ?? '-',
            $item->asal_usul ?? '-',
            $item->jangka_waktu ?? '-',
            $harga,
            $item->kondisi ?? '-',
            $dokumentasi,
            $item->user ?? '-',
            $item->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0'],
                ],
            ],
        ];
    }
}
