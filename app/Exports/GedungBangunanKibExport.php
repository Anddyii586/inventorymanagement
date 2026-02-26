<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GedungBangunanKibExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
                'Jenis Barang/Nama Barang',
                'Kode Barang',
                'Register',
                'Kondisi B',
                'Kondisi KB',
                'Kondisi RB',
                'Bertingkat/Tidak',
                'Beton/Tidak',
                'Luas Lantai (M2)',
                'Letak/Alamat',
                'Tahun Pengadaan',
                'Sistem Tanah',
                'Nomor Sertifikat Tanah',
                'Asal-usul Tanah',
                'Harga (Rp.)',
                'PIC',
                'Dokumentasi',
                'Keterangan',
            ];
        }
        
        return [
            'No',
            'Jenis Barang/Nama Barang',
            'Kode Barang',
            'Register',
            'Kondisi B',
            'Kondisi KB',
            'Kondisi RB',
            'Bertingkat/Tidak',
            'Beton/Tidak',
            'Luas Lantai (M2)',
            'Letak/Alamat',
            'Tahun Pengadaan',
            'Luas Tanah (M2)',
            'Status Tanah',
            'Nomor Sertifikat',
            'Asal-usul',
            'Harga (Rp.)',
            'PIC',
            'Dokumentasi',
            'Keterangan',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;
        
        $subSubKelompok = $item->subSubKelompok()->first();
        $tahun = $item->tahun ?? ($item->tanggal_pengadaan ? \Carbon\Carbon::parse($item->tanggal_pengadaan)->format('Y') : '-');
        $harga = $item->harga ? number_format($item->harga, 0, ',', '.') : '-';
        $dokumentasi = $item->hasDokumentasi() ? 'Ada' : '-';
        $kondisiB = $item->kondisi == 'Baik' ? '✓' : '';
        $kondisiKB = $item->kondisi == 'Kurang Baik' ? '✓' : '';
        $kondisiRB = $item->kondisi == 'Rusak Berat' ? '✓' : '';
        $bertingkat = $item->bertingkat ? 'Bertingkat' : 'Tidak';
        $beton = $item->beton ? 'Beton' : 'Tidak';
        
        if ($this->type === 'idle') {
            return [
                $no,
                $subSubKelompok->sub_sub_kelompok ?? 'Gedung/Bangunan',
                $subSubKelompok->id ?? '-',
                substr($item->id ?? '-', -4),
                $kondisiB,
                $kondisiKB,
                $kondisiRB,
                $bertingkat,
                $beton,
                $item->luas_lantai ?? '-',
                $item->letak ?? '-',
                $tahun,
                $item->status_tanah ?? '-',
                $item->nomor_sertifikat ?? '-',
                $item->asal_usul ?? '-',
                $harga,
                $item->user ?? '-',
                $dokumentasi,
                $item->keterangan ?? '-',
            ];
        }
        
        return [
            $no,
            $subSubKelompok->sub_sub_kelompok ?? 'Gedung/Bangunan',
            $subSubKelompok->id ?? '-',
            substr($item->id ?? '-', -4),
            $kondisiB,
            $kondisiKB,
            $kondisiRB,
            $bertingkat,
            $beton,
            $item->luas_lantai ?? '-',
            $item->letak ?? '-',
            $tahun,
            $item->luas_tanah ?? '-',
            $item->status_tanah ?? '-',
            $item->nomor_sertifikat ?? '-',
            $item->asal_usul ?? '-',
            $harga,
            $item->subBidang?->sub_bidang ?? '-',
            $dokumentasi,
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
