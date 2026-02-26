<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PeralatanMesinRusakBeratExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Flatten grouped data
        $flattened = collect();
        foreach ($this->data as $group => $items) {
            foreach ($items as $item) {
                $flattened->push($item);
            }
        }
        return $flattened;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama/Jenis Barang',
            'Merk/Type/Alamat',
            'Asal/Cara Perolehan',
            'Tahun Perolehan',
            'Jumlah Barang',
            'Harga',
            'Nilai Buku',
            'Keterangan',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;
        
        $subSubKelompok = $item->subSubKelompok()->first();
        $harga = $item->harga ? number_format($item->harga, 0, ',', '.') : '-';
        $nilaiBuku = $item->harga ? number_format($item->harga, 0, ',', '.') : '-'; // Assuming nilai buku = harga
        $tahunPerolehan = $item->tahun_pembelian ?? $item->tahun ?? '-';
        
        return [
            $no,
            $subSubKelompok->id ?? '-',
            $item->nama_barang ?? '-',
            $item->merek ?? '-',
            $item->asal_usul ?? '-',
            $tahunPerolehan,
            1, // Jumlah Barang
            $harga,
            $nilaiBuku,
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
