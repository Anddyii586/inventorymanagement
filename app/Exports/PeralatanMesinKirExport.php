<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PeralatanMesinKirExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        if ($this->data && $this->data->count() > 0) {
            foreach ($this->data as $group => $items) {
                if (is_iterable($items)) {
                    foreach ($items as $item) {
                        // Ensure subSubKelompok is loaded
                        if ($item && !isset($item->subSubKelompok) && method_exists($item, 'subSubKelompok')) {
                            $item->load('subSubKelompok.subKelompok');
                        }
                        $flattened->push($item);
                    }
                }
            }
        }
        return $flattened;
    }

    public function headings(): array
    {
        return [
            'No',
            'Jenis Barang',
            'Merk/Model',
            'No Seri Pabrik',
            'Ukuran',
            'Bahan',
            'Tahun Pembelian',
            'Kode Lokasi',
            'Kode Barang',
            'Jumlah',
            'Harga Perolehan',
            'Keadaan Baik',
            'Keadaan Kurang Baik',
            'Keadaan Rusak Berat',
            'Keterangan Mutasi dan lain lain',
            'Kode Dokumentasi',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;
        
        $harga = $item->harga ? number_format($item->harga, 0, ',', '.') : '-';
        $kondisiBaik = $item->kondisi == 'Baik' ? '1' : '';
        $kondisiKB = $item->kondisi == 'Kurang Baik' ? '1' : '';
        $kondisiRB = $item->kondisi == 'Rusak Berat' ? '1' : '';
        
        // Handle subSubKelompok relationship
        $subSubKelompok = null;
        if (isset($item->subSubKelompok)) {
            $subSubKelompok = $item->subSubKelompok;
        } elseif (method_exists($item, 'subSubKelompok')) {
            $subSubKelompok = $item->subSubKelompok()->first();
        }
        
        return [
            $no,
            $subSubKelompok->sub_sub_kelompok ?? '-',
            $item->merek ?? '-',
            $item->no_seri_pabrik ?? '-',
            $item->spesifikasi ?? '-',
            $item->bahan ?? '-',
            $item->tahun_pembelian ?? $item->tahun ?? '-',
            $item->kode_lokasi ?? '-',
            $item->id ?? '-',
            $item->jumlah ?? 1,
            $harga,
            $kondisiBaik,
            $kondisiKB,
            $kondisiRB,
            $item->keterangan ?? '-',
            '', // Kode Dokumentasi
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
