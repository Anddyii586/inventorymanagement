<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PeralatanMesinKibExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $kategori;

    public function __construct($data, $kategori = 'Peralatan')
    {
        $this->data = $data;
        $this->kategori = $kategori;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        if ($this->kategori === 'Kendaraan Dinas') {
            return [
                'No',
                'Kode Barang',
                'Jenis Barang/Nama Barang',
                'Nomor Register',
                'Merk/Type',
                'Ukuran/CC',
                'Bahan',
                'Tahun Pembelian',
                'Nomor Pabrik',
                'Nomor Rangka',
                'Nomor Mesin',
                'Nomor Polisi',
                'BPKB',
                'Asal-usul Perolehan',
                'Harga (Rp)',
                'Kondisi B',
                'Kondisi KB',
                'Kondisi RB',
                'PIC',
                'Dokumentasi',
                'Keterangan',
            ];
        } elseif ($this->kategori === 'Pompa') {
            return [
                'No',
                'Kode Barang',
                'Jenis Barang/Nama Barang',
                'Nomor Register',
                'Merk/Type',
                'Ukuran/CC',
                'Bahan',
                'Tahun Pembelian',
                'Nomor Pabrik',
                'Nomor Rangka',
                'Nomor Mesin',
                'Nomor Polisi',
                'BPKB',
                'Asal-usul Perolehan',
                'Harga (Rp)',
                'Kondisi B',
                'Kondisi KB',
                'Kondisi RB',
                'RTU',
                'Panel Listrik',
                'Rumah Panel',
                'PIC',
                'Dokumentasi',
                'Keterangan',
            ];
        }
        
        // Default untuk Peralatan
        return [
            'No',
            'Kode Barang',
            'Jenis Barang/Nama Barang',
            'Nomor Register',
            'Merk/Type',
            'Ukuran/CC',
            'Bahan',
            'Tahun Pembelian',
            'Nomor Pabrik',
            'Nomor Rangka',
            'Nomor Mesin',
            'Asal-usul Perolehan',
            'Harga (Rp)',
            'Kondisi B',
            'Kondisi KB',
            'Kondisi RB',
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
        $harga = $item->harga ? number_format($item->harga, 0, ',', '.') : '-';
        $dokumentasi = $item->dokumentasi ? 'Ada' : 'Tidak Ada';
        $kondisiB = $item->kondisi == 'Baik' ? '✓' : '';
        $kondisiKB = $item->kondisi == 'Kurang Baik' ? '✓' : '';
        $kondisiRB = $item->kondisi == 'Rusak Berat' ? '✓' : '';
        $tahunPembelian = $item->tahun_pembelian ?? $item->tahun ?? '-';
        
        if ($this->kategori === 'Kendaraan Dinas') {
            return [
                $no,
                $subSubKelompok->id ?? '-',
                $item->nama_barang ?? '-',
                substr($item->id ?? '-', -4),
                $item->merek ?? '-',
                $item->spesifikasi ?? '-',
                $item->bahan ?? '-',
                $tahunPembelian,
                $item->nomor_pabrik ?? '-',
                $item->nomor_rangka ?? '-',
                $item->nomor_mesin ?? '-',
                $item->nomor_polisi ?? '-',
                $item->bpkb ?? '-',
                $item->asal_usul ?? '-',
                $harga,
                $kondisiB,
                $kondisiKB,
                $kondisiRB,
                $item->subBidang?->sub_bidang ?? '-',
                $dokumentasi,
                $item->keterangan ?? '-',
            ];
        } elseif ($this->kategori === 'Pompa') {
            return [
                $no,
                $subSubKelompok->id ?? '-',
                $item->nama_barang ?? '-',
                substr($item->id ?? '-', -4),
                $item->merek ?? '-',
                $item->spesifikasi ?? '-',
                $item->bahan ?? '-',
                $tahunPembelian,
                $item->nomor_pabrik ?? '-',
                $item->nomor_rangka ?? '-',
                $item->nomor_mesin ?? '-',
                $item->nomor_polisi ?? '-',
                $item->bpkb ?? '-',
                $item->asal_usul ?? '-',
                $harga,
                $kondisiB,
                $kondisiKB,
                $kondisiRB,
                $item->rtu ? 'Ya' : 'Tidak',
                $item->panel_listrik ? 'Ya' : 'Tidak',
                $item->rumah_panel ? 'Ya' : 'Tidak',
                $item->subBidang?->sub_bidang ?? '-',
                $dokumentasi,
                $item->keterangan ?? '-',
            ];
        }
        
        // Default untuk Peralatan
        return [
            $no,
            $subSubKelompok->id ?? '-',
            $item->nama_barang ?? '-',
            substr($item->id ?? '-', -4),
            $item->merek ?? '-',
            $item->spesifikasi ?? '-',
            $item->bahan ?? '-',
            $tahunPembelian,
            $item->nomor_pabrik ?? '-',
            $item->nomor_rangka ?? '-',
            $item->nomor_mesin ?? '-',
            $item->asal_usul ?? '-',
            $harga,
            $kondisiB,
            $kondisiKB,
            $kondisiRB,
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
