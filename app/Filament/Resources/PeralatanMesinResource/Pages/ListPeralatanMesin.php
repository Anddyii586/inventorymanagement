<?php

namespace App\Filament\Resources\PeralatanMesinResource\Pages;

use Filament\Forms;
use Filament\Actions;
use App\Models\Lokasi\Ruangan;
use App\Services\KodifikasiService;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PeralatanMesinResource;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPeralatanMesin extends ListRecords
{
    protected static string $resource = PeralatanMesinResource::class;

    public function getTabs(): array
    {
        return [
            'peralatan' => Tab::make('Peralatan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori', 'Peralatan')),
            'kendaraan-dinas' => Tab::make('Kendaraan Dinas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori', 'Kendaraan Dinas')),
            'pompa' => Tab::make('Pompa')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori', 'Pompa')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'peralatan';
    }

    protected function getHeaderActions(): array
    {
        $ruanganList = Ruangan::pluck('nama', 'id')->toArray();
        $kodeLokasi = fn($set, $get) => $set('kode_lokasi', KodifikasiService::kodeLokasi($get('wilayah_id'), $get('sub_bidang_id'), $get('unit_id'), $get('tahun')));

        // Form KIR - digunakan untuk cetak dan export
        $kirForm = [
            Forms\Components\Select::make('ruangan')
                ->label('Pilih Ruangan')
                ->options($ruanganList)
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('unit_id')
                ->label('Unit')
                ->options(\App\Models\Lokasi\Unit::pluck('unit', 'id'))
                ->searchable()
                ->preload()
                ->placeholder('Semua Unit')
                ->nullable(),
            Forms\Components\Select::make('sub_bidang_id')
                ->label('Sub Bidang')
                ->options(\App\Models\Lokasi\SubBidang::pluck('sub_bidang', 'id'))
                ->searchable()
                ->preload()
                ->placeholder('Semua Sub Bidang')
                ->nullable(),
            Forms\Components\CheckboxList::make('kategori')
                ->label('Pilih Kategori')
                ->options([
                    'Peralatan' => 'Peralatan',
                    'Kendaraan Dinas' => 'Kendaraan Dinas',
                    'Pompa' => 'Pompa',
                ])
                ->default(['Peralatan'])
                ->columns(3)
                ->required(),
            Forms\Components\Select::make('penanggung_jawab')
                ->label('Penanggung Jawab')
                ->options(function () {
                    return \App\Models\User::pluck('nama', 'id')->toArray();
                })
                ->searchable()
                ->placeholder('Pilih Penanggung Jawab')
                ->nullable(),
            Forms\Components\Toggle::make('gabungkan_aset')
                ->label('Gabungkan Aset yang Sama')
                ->default(false)
                ->live()
                ->afterStateUpdated(function ($state, $set) {
                    if (!$state) {
                        $set('kolom_gabungan', []);
                    }
                }),
            Forms\Components\CheckboxList::make('kolom_gabungan')
                ->label('Berdasarkan Kolom')
                ->options([
                    'merek' => 'Merk/Model',
                    'no_seri_pabrik' => 'No Seri Pabrik',
                    'spesifikasi' => 'Ukuran',
                    'bahan' => 'Bahan',
                    'tahun' => 'Tahun Pembelian',
                ])
                ->default(['merek', 'no_seri_pabrik', 'spesifikasi', 'bahan', 'tahun'])
                ->columns(2)
                ->visible(fn ($get) => $get('gabungkan_aset'))
                ->required(fn ($get) => $get('gabungkan_aset')),
        ];

        // Form KIB - digunakan untuk cetak dan export
        $kibForm = [
            Forms\Components\Radio::make('kategori')
                ->label('Kategori')
                ->options([
                    'Peralatan' => 'Peralatan',
                    'Kendaraan Dinas' => 'Kendaraan Dinas',
                    'Pompa' => 'Pompa',
                ])
                ->inline()
                ->default('Peralatan')
                ->live()
                ->required(),
            Forms\Components\Select::make('penanggung_jawab')
                ->label('Penanggung Jawab')
                ->options(function () {
                    return \App\Models\User::pluck('nama', 'id')->toArray();
                })
                ->searchable()
                ->placeholder('Pilih Penanggung Jawab')
                ->nullable(),
            Forms\Components\Toggle::make('input_mode')
                ->label('Input Manual')
                ->default(false)
                ->live(),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('wilayah_id')
                        ->label('Wilayah')
                        ->options(\App\Models\Lokasi\Wilayah::pluck('wilayah', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Wilayah')
                        ->live()
                        ->afterStateUpdated($kodeLokasi)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('bidang_id')
                        ->label('Bidang')
                        ->options(\App\Models\Lokasi\Bidang::pluck('bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Bidang')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('sub_bidang_id')
                        ->label('Sub Bidang')
                        ->options(\App\Models\Lokasi\SubBidang::pluck('sub_bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Bidang')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('unit_id')
                        ->label('Unit')
                        ->options(\App\Models\Lokasi\Unit::pluck('unit', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Unit')
                        ->live()
                        ->afterStateUpdated($kodeLokasi)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('tahun')
                        ->label('Tahun Penempatan')
                        ->numeric()
                        ->minValue(1901)
                        ->maxValue(date('Y'))
                        ->placeholder('Semua Tahun')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('tahun_pembelian')
                        ->label('Tahun Pembelian')
                        ->options(function () {
                            $years = [];
                            $currentYear = date('Y');
                            for ($i = $currentYear; $i >= 1950; $i--) {
                                $years[$i] = $i;
                            }
                            return $years;
                        })
                        ->searchable()
                        ->placeholder('Semua Tahun Pembelian')
                        ->nullable(),
                ])
                ->visible(fn($get) => !$get('input_mode')),
            Forms\Components\TextInput::make('kode_lokasi')
                ->label('Kode Lokasi')
                ->placeholder('Masukkan kode lokasi manual')
                ->readOnly(fn($get) => !$get('input_mode'))
                ->visible(fn($get) => $get('input_mode')),
        ];

        // Form Barang Rusak Berat - digunakan untuk cetak dan export
        $rusakBeratForm = [
            Forms\Components\Select::make('penanggung_jawab')
                ->label('Penanggung Jawab')
                ->options(function () {
                    return \App\Models\User::pluck('nama', 'id')->toArray();
                })
                ->searchable()
                ->placeholder('Pilih Penanggung Jawab')
                ->nullable(),
            Forms\Components\Toggle::make('input_mode')
                ->label('Input Manual')
                ->default(false)
                ->live(),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('wilayah_id')
                        ->label('Wilayah')
                        ->options(\App\Models\Lokasi\Wilayah::pluck('wilayah', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Wilayah')
                        ->live()
                        ->afterStateUpdated($kodeLokasi)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('bidang_id')
                        ->label('Bidang')
                        ->options(\App\Models\Lokasi\Bidang::pluck('bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Bidang')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('sub_bidang_id')
                        ->label('Sub Bidang')
                        ->options(\App\Models\Lokasi\SubBidang::pluck('sub_bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Bidang')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('unit_id')
                        ->label('Unit')
                        ->options(\App\Models\Lokasi\Unit::pluck('unit', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Unit')
                        ->live()
                        ->afterStateUpdated($kodeLokasi)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('tahun')
                        ->label('Tahun Penempatan')
                        ->numeric()
                        ->minValue(1901)
                        ->maxValue(date('Y'))
                        ->placeholder('Semua Tahun')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('tahun_pembelian')
                        ->label('Tahun Pembelian')
                        ->options(function () {
                            $years = [];
                            $currentYear = date('Y');
                            for ($i = $currentYear; $i >= 1950; $i--) {
                                $years[$i] = $i;
                            }
                            return $years;
                        })
                        ->searchable()
                        ->placeholder('Semua Tahun Pembelian')
                        ->nullable(),
                ])
                ->visible(fn($get) => !$get('input_mode')),
            Forms\Components\TextInput::make('kode_lokasi')
                ->label('Kode Lokasi')
                ->placeholder('Masukkan kode lokasi manual')
                ->readOnly(fn($get) => !$get('input_mode'))
                ->visible(fn($get) => $get('input_mode')),
        ];

        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('KIR')
                    ->label('Kartu Inventaris Ruangan')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form($kirForm)
                    ->action(function (array $data) {
                        $ruanganId = $data['ruangan'];
                        $kategoriKib = $data['kategori'] ?? [];
                        $queryParams = [
                            'ruangan' => $ruanganId,
                            'kategori' => $kategoriKib,
                        ];
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if (!empty($data['sub_bidang_id'])) {
                            $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                        }
                        
                        if (!empty($data['unit_id'])) {
                            $queryParams['unit_id'] = $data['unit_id'];
                        }
                        
                        if (!empty($data['gabungkan_aset']) && !empty($data['kolom_gabungan'])) {
                            $queryParams['gabungkan_aset'] = $data['gabungkan_aset'];
                            $queryParams['kolom_gabungan'] = $data['kolom_gabungan'];
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('peralatan-mesin.cetak-kir') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Cetak'),
                Actions\Action::make('KIB')
                    ->label('Kartu Inventaris Barang')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form($kibForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        // Add kategori parameter
                        if (!empty($data['kategori'])) {
                            $queryParams['kategori'] = $data['kategori'];
                        }
                        
                        // Add penanggung jawab parameter
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if ($data['input_mode'] && !empty($data['kode_lokasi'])) {
                            $queryParams['kode_lokasi'] = $data['kode_lokasi'];
                        } else {
                            // Add individual filter parameters
                            if (!empty($data['wilayah_id'])) {
                                $queryParams['wilayah_id'] = $data['wilayah_id'];
                            }
                            if (!empty($data['bidang_id'])) {
                                $queryParams['bidang_id'] = $data['bidang_id'];
                            }
                            if (!empty($data['sub_bidang_id'])) {
                                $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                            }
                            if (!empty($data['unit_id'])) {
                                $queryParams['unit_id'] = $data['unit_id'];
                            }
                            if (!empty($data['tahun'])) {
                                $queryParams['tahun'] = $data['tahun'];
                            }
                            if (!empty($data['tahun_pembelian'])) {
                                $queryParams['tahun_pembelian'] = $data['tahun_pembelian'];
                            }
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('peralatan-mesin.cetak-kib') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Cetak'),
                Actions\Action::make('QR')
                    // ->visible(false)
                    ->label('QR Label')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->form([
                        Forms\Components\CheckboxList::make('cetak_by')
                            ->label('Cetak Berdasarkan')
                            ->options([
                                'by_ruangan' => 'Per Ruangan',
                                'by_sub_kelompok' => 'Per Sub Kelompok',
                                'by_ids' => 'Pilih Aset Manual'
                            ])
                            ->default(['by_ids'])
                            ->live()
                            ->required()
                            ->columns(3),
                        Forms\Components\Select::make('ruangan_id')
                            ->label('Pilih Ruangan')
                            ->options(Ruangan::pluck('nama', 'id'))
                            ->visible(fn($get) => in_array('by_ruangan', $get('cetak_by') ?? []))
                            ->searchable()
                            ->multiple()
                            ->required(fn($get) => in_array('by_ruangan', $get('cetak_by') ?? [])),
                        Forms\Components\Select::make('sub_sub_kelompok_id')
                            ->label('Pilih Sub Sub Kelompok')
                            ->options(\App\Models\Aset\SubSubKelompok::where('id', 'like', '03.%')->pluck('sub_sub_kelompok', 'id'))
                            ->visible(fn($get) => in_array('by_sub_kelompok', $get('cetak_by') ?? []))
                            ->searchable()
                            ->multiple()
                            ->required(fn($get) => in_array('by_sub_kelompok', $get('cetak_by') ?? [])),
                        Forms\Components\Select::make('selected_ids')
                            ->label('Pilih Aset')
                            ->options(function() {
                                return \App\Models\PeralatanMesin::with(['subSubKelompok'])
                                    ->get()
                                    ->mapWithKeys(function($item) {
                                        return [$item->id => $item->id . ' - ' . $item->nama_barang . ' - ' . ($item->subSubKelompok()->first()->sub_sub_kelompok ?? 'N/A')];
                                    });
                            })
                            ->visible(fn($get) => in_array('by_ids', $get('cetak_by') ?? []))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(fn($get) => in_array('by_ids', $get('cetak_by') ?? [])),
                        Forms\Components\Radio::make('printer_mode')
                            ->label('Mode Printer')
                            ->options([
                                // 'direct' => 'Direct Print (Console)',
                                // 'zpl_file' => 'Generate ZPL File',
                                // 'thermal' => 'Thermal Printer (HTML)',
                                'normal' => 'Printer Biasa'
                            ])
                            ->default('normal')
                            ->inline()
                            ->required(),
                        Forms\Components\Radio::make('paper_size')
                            ->label('Ukuran Kertas')
                            ->options([
                                '10x5' => '10 x 5 cm',
                                '10x2' => '10 x 2 cm'
                            ])
                            ->default('10x5')
                            ->inline()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $cetakBy = $data['cetak_by'] ?? [];
                        
                        // Start with base query
                        $query = \App\Models\PeralatanMesin::query();
                        
                        // Apply filters based on selected criteria (AND logic)
                        if (in_array('by_ruangan', $cetakBy) && !empty($data['ruangan_id'])) {
                            $ruanganIds = is_array($data['ruangan_id']) ? $data['ruangan_id'] : [$data['ruangan_id']];
                            $query->whereIn('ruangan_id', $ruanganIds);
                        }
                        
                        if (in_array('by_sub_kelompok', $cetakBy) && !empty($data['sub_sub_kelompok_id'])) {
                            $subKelompokIds = is_array($data['sub_sub_kelompok_id']) ? $data['sub_sub_kelompok_id'] : [$data['sub_sub_kelompok_id']];
                            $query->whereIn('sub_sub_kelompok_id', $subKelompokIds);
                        }
                        
                        if (in_array('by_ids', $cetakBy) && !empty($data['selected_ids'])) {
                            $manualIds = is_array($data['selected_ids']) ? $data['selected_ids'] : [$data['selected_ids']];
                            $query->whereIn('id', $manualIds);
                        }
                        
                        // Get the filtered asset IDs
                        $selectedIds = $query->pluck('id')->toArray();
                        
                        if (empty($selectedIds)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada aset yang memenuhi kriteria yang dipilih')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        if ($data['printer_mode'] === 'direct') {
                            // Direct print using console command
                            $exitCode = \Illuminate\Support\Facades\Artisan::call('print:qr-codes', [
                                'ids' => implode(',', $selectedIds),
                                '--printer' => 'SATO_CG408'
                            ]);
                            
                            $output = \Illuminate\Support\Facades\Artisan::output();
                            
                            if ($exitCode === 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Print job sent successfully')
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Failed to print')
                                    ->body($output)
                                    ->danger()
                                    ->send();
                            }
                        } elseif ($data['printer_mode'] === 'zpl_file') {
                            // Generate ZPL file
                            $response = \Illuminate\Support\Facades\Http::post(route('print.generate-zpl'), [
                                'ids' => implode(',', $selectedIds)
                            ]);
                            
                            if ($response->successful()) {
                                $data = $response->json();
                                \Filament\Notifications\Notification::make()
                                    ->title('ZPL file generated')
                                    ->body('File saved as: ' . $data['filename'])
                                    ->success()
                                    ->send();
                                
                                // Download the file
                                return redirect()->away($data['download_url']);
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Failed to generate ZPL file')
                                    ->danger()
                                    ->send();
                            }
                        } else {
                            // HTML print (thermal or normal)
                            $url = route('peralatan-mesin.cetak-qr', [
                                'ids' => implode(',', $selectedIds),
                                'thermal' => $data['printer_mode'] === 'thermal' ? 'true' : 'false',
                                'paper_size' => $data['paper_size'] ?? '10x5'
                            ]);
                            return redirect()->away($url);
                        }
                    }),
                Actions\Action::make('Barang Rusak Berat')
                    ->label('Barang Rusak Berat')
                    ->icon('heroicon-o-printer')
                    ->color('danger')
                    ->form($rusakBeratForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        // Add penanggung jawab parameter
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if ($data['input_mode'] && !empty($data['kode_lokasi'])) {
                            $queryParams['kode_lokasi'] = $data['kode_lokasi'];
                        } else {
                            // Add individual filter parameters
                            if (!empty($data['wilayah_id'])) {
                                $queryParams['wilayah_id'] = $data['wilayah_id'];
                            }
                            if (!empty($data['bidang_id'])) {
                                $queryParams['bidang_id'] = $data['bidang_id'];
                            }
                            if (!empty($data['sub_bidang_id'])) {
                                $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                            }
                            if (!empty($data['unit_id'])) {
                                $queryParams['unit_id'] = $data['unit_id'];
                            }
                            if (!empty($data['tahun'])) {
                                $queryParams['tahun'] = $data['tahun'];
                            }
                            if (!empty($data['tahun_pembelian'])) {
                                $queryParams['tahun_pembelian'] = $data['tahun_pembelian'];
                            }
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('peralatan-mesin.cetak-rusak-berat') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Cetak'),
            ])
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->button(),
            Actions\ActionGroup::make([
                Actions\Action::make('KIR Export')
                    ->label('Kartu Inventaris Ruangan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form($kirForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        if (!empty($data['ruangan'])) {
                            $queryParams['ruangan'] = $data['ruangan'];
                        }
                        
                        // Handle array parameter correctly - http_build_query handles arrays automatically
                        if (!empty($data['kategori']) && is_array($data['kategori'])) {
                            $queryParams['kategori'] = $data['kategori'];
                        }
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if (!empty($data['sub_bidang_id'])) {
                            $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                        }
                        
                        if (!empty($data['unit_id'])) {
                            $queryParams['unit_id'] = $data['unit_id'];
                        }
                        
                        if (!empty($data['gabungkan_aset']) && !empty($data['kolom_gabungan'])) {
                            $queryParams['gabungkan_aset'] = $data['gabungkan_aset'];
                            if (is_array($data['kolom_gabungan'])) {
                                $queryParams['kolom_gabungan'] = $data['kolom_gabungan'];
                            }
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('peralatan-mesin.export-kir') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Export'),
                Actions\Action::make('KIB Export')
                    ->label('Kartu Inventaris Barang')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form($kibForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        // Add kategori parameter
                        if (!empty($data['kategori'])) {
                            $queryParams['kategori'] = $data['kategori'];
                        }
                        
                        // Add penanggung jawab parameter
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if ($data['input_mode'] && !empty($data['kode_lokasi'])) {
                            $queryParams['kode_lokasi'] = $data['kode_lokasi'];
                        } else {
                            // Add individual filter parameters
                            if (!empty($data['wilayah_id'])) {
                                $queryParams['wilayah_id'] = $data['wilayah_id'];
                            }
                            if (!empty($data['bidang_id'])) {
                                $queryParams['bidang_id'] = $data['bidang_id'];
                            }
                            if (!empty($data['sub_bidang_id'])) {
                                $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                            }
                            if (!empty($data['unit_id'])) {
                                $queryParams['unit_id'] = $data['unit_id'];
                            }
                            if (!empty($data['tahun'])) {
                                $queryParams['tahun'] = $data['tahun'];
                            }
                            if (!empty($data['tahun_pembelian'])) {
                                $queryParams['tahun_pembelian'] = $data['tahun_pembelian'];
                            }
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('peralatan-mesin.export-kib') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Export'),
                Actions\Action::make('Barang Rusak Berat Export')
                    ->label('Barang Rusak Berat')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->form($rusakBeratForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if ($data['input_mode'] && !empty($data['kode_lokasi'])) {
                            $queryParams['kode_lokasi'] = $data['kode_lokasi'];
                        } else {
                            // Add individual filter parameters
                            if (!empty($data['wilayah_id'])) {
                                $queryParams['wilayah_id'] = $data['wilayah_id'];
                            }
                            if (!empty($data['bidang_id'])) {
                                $queryParams['bidang_id'] = $data['bidang_id'];
                            }
                            if (!empty($data['sub_bidang_id'])) {
                                $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                            }
                            if (!empty($data['unit_id'])) {
                                $queryParams['unit_id'] = $data['unit_id'];
                            }
                            if (!empty($data['tahun'])) {
                                $queryParams['tahun'] = $data['tahun'];
                            }
                            if (!empty($data['tahun_pembelian'])) {
                                $queryParams['tahun_pembelian'] = $data['tahun_pembelian'];
                            }
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('peralatan-mesin.export-rusak-berat') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Export'),
            ])
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->button(),
        ];
    }
}
