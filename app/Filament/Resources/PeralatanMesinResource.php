<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PeralatanMesin;
use App\Models\Lokasi\SubBidang;
use Filament\Resources\Resource;
use App\Services\KodifikasiService;
use App\Filament\Resources\PeralatanMesinResource\Pages;
use App\Filament\Resources\RelationManagers;
use Filament\Support\RawJs;

class PeralatanMesinResource extends Resource
{
    protected static ?string $model = PeralatanMesin::class;

    protected static ?string $slug = 'peralatan-mesin';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Peralatan & Mesin';

    public static function form(Form $form): Form
    {
        $kodeLokasi = fn($set, $get) => $set('kode_lokasi', KodifikasiService::kodeLokasi($get('wilayah_id'), $get('sub_bidang_id'), $get('unit_id'), $get('tahun')));
        $kodeBarang = fn($set, $get, $record) => $set('id', KodifikasiService::kodeBarang($record, $get('sub_sub_kelompok_id'), $get('tanggal_pengadaan'), PeralatanMesin::class, $get('tahun_pembelian')));
        $updateNamaBarang = fn($set, $get) => $set('nama_barang', \App\Models\Aset\SubSubKelompok::find($get('sub_sub_kelompok_id'))?->sub_sub_kelompok ?? '');
        return $form
            ->schema([
                Forms\Components\Radio::make('kategori')
                    ->options([
                        'Peralatan' => 'Peralatan',
                        'Kendaraan Dinas' => 'Kendaraan Dinas',
                        'Pompa' => 'Pompa'
                    ])
                    ->descriptions([
                        'Peralatan' => 'Alat-alat Besar Darat, Alat-alat Besar Apung, Alat-alat Bantu, Alat Bengkel, Alat Kantor Alat Studio, Alat Laboratorium, dan lain-lain sejenisnya.',
                        'Kendaraan Dinas' => 'Alat Angkutan Darat Bermotor, Alat Angkutan Darat Tak Bermotor, Alat Angkut Apung Bermotor, Alat Angkut Apung tak Bermotor, Alat Angkut Bermotor Udara, dan lain-lain sejenisnya.',
                        'Pompa' => 'Alat perpompaan, Genset, dan lain-lain sejenisnya.'
                    ])
                    ->inline()
                    ->default('Peralatan')
                    ->live()
                    ->required()
                    ->label('')
                    ->columnSpanFull(),
                Forms\Components\Section::make('Kode Lokasi')->schema([
                    Forms\Components\Select::make('wilayah_id')
                        ->relationship(name: 'wilayah', titleAttribute: 'wilayah')
                        ->live()
                        ->afterStateUpdated($kodeLokasi)
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('bidang_id')
                        ->label('Bidang')
                        ->options(
                            \App\Models\Lokasi\Bidang::query()
                                ->when(auth()->user()->akses == 'User', function ($query) {
                                    $wilayah = ['Mataram', 'Gunungsari', 'Gerung'];
                                    $sub_bidang = auth()->user()->subBidang->sub_bidang;
                                    foreach ($wilayah as $w) {
                                        if (str_contains($sub_bidang, $w)) {
                                            return $query->where(function ($query) use ($w) {
                                                $query->whereHas('subBidang', function ($query) use ($w) {
                                                    $query->where('sub_bidang', 'like', '%' . $w . '%');
                                                })->orWhere('bidang', 'Kehilangan Air');
                                            });
                                        }
                                    }
                                    return $query->where('id', auth()->user()->bidang_id);
                                })
                                ->pluck('bidang', 'id')
                        )
                        ->default(function ($record) {
                            if (!$record)
                                return null;
                            $subBidang = SubBidang::where('id', $record->sub_bidang_id)->first();
                            return $subBidang ? $subBidang->id_bidang : null;
                        })
                        ->live()
                        ->searchable()
                        ->preload()
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if (!$record)
                                return;
                            $subBidang = SubBidang::where('id', $record->sub_bidang_id)->first();
                            if ($subBidang) {
                                $component->state($subBidang->id_bidang);
                            }
                        })
                        ->afterStateUpdated(function ($set) {
                            $set('sub_bidang_id', null);
                        })
                        ->dehydrated(false),
                    Forms\Components\Select::make('sub_bidang_id')
                        ->relationship(
                            name: 'subBidang',
                            titleAttribute: 'sub_bidang',
                            modifyQueryUsing: fn($query, $get) => $query->when(
                                $get('bidang_id'),
                                fn($query, $bidang_id) => $query->where('id_bidang', $bidang_id)
                            )->when(auth()->user()->akses == 'User', function ($query) {
                                $wilayah = ['Mataram', 'Gunungsari', 'Gerung'];
                                $sub_bidang = auth()->user()->subBidang->sub_bidang;
                                foreach ($wilayah as $w) {
                                    if (str_contains($sub_bidang, $w)) {
                                        return $query->where(function($query) use ($w) {
                                            $query->where('sub_bidang', 'like', '%' . $w . '%')->orWhereHas('bidang', function($query) {
                                                $query->where('bidang', 'Kehilangan Air');
                                            });
                                        });
                                    }
                                }
                                return $query->where('id_bidang', auth()->user()->bidang_id);
                            })
                        )
                        ->default(fn($record) => $record?->sub_bidang_id)
                        ->live()
                        ->afterStateUpdated($kodeLokasi)
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('unit_id')
                        ->relationship(name: 'unit', titleAttribute: 'unit')
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('ruangan_id', null);
                        })
                        ->afterStateUpdated($kodeLokasi)
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('tahun')
                        ->label('Tahun penempatan')
                        ->live(true)
                        ->afterStateUpdated($kodeLokasi)
                        ->numeric()
                        ->minValue(1950)
                        ->maxValue(date('Y')),
                    Forms\Components\TextInput::make('kode_lokasi')
                        ->readOnly()
                        ->helperText('Kode Lokasi diisi otomatis oleh sistem')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('ruangan_id')
                        ->label('Ruangan')
                        ->options(function ($get) {
                            $unitId = $get('unit_id');
                            $query = \App\Models\Lokasi\Ruangan::query();
                            if ($unitId) {
                                $query->where('unit_id', $unitId);
                            }
                            return $query->pluck('nama', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpanFull(),
                ])->columns(),
                Forms\Components\Section::make('Kode Aset')->schema([
                    
                    Forms\Components\Select::make('sub_sub_kelompok_id')
                        ->live()
                        ->afterStateUpdated($kodeBarang)
                        ->afterStateUpdated($updateNamaBarang)
                        ->relationship(name: 'subSubKelompok', modifyQueryUsing: fn($query) => $query->where('id', 'like', '03.%'))
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->id} {$record->sub_sub_kelompok}")
                        ->searchable(['id', 'sub_sub_kelompok'])
                        ->preload(),
                    Forms\Components\TextInput::make('tahun_pembelian')
                        ->label('Tahun Pembelian')
                        ->numeric()
                        ->minValue(1950)
                        ->maxValue(date('Y'))
                        ->live()
                        ->afterStateUpdated($kodeBarang),
                    Forms\Components\TextInput::make('id')
                        ->label('ID')
                        ->readOnly()
                        ->required()
                        ->validationAttribute('ID')
                        ->helperText('Kode Barang diisi otomatis oleh sistem'),
                    Forms\Components\TextInput::make('nama_barang')
                        ->label('Nama Barang')
                        ->required()
                        ->maxLength(255),
                ])->columns(),
                Forms\Components\Section::make('Rincian Peralatan & Mesin')->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('tipe')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('merek')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bahan')
                            ->maxLength(255),
                    ]),
                    Forms\Components\Textarea::make('spesifikasi')
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('tanggal_pengadaan'),
                    Forms\Components\Select::make('asal_usul')
                        ->options([
                            'Pembelian' => 'Pembelian',
                            'Pengadaan' => 'Pengadaan',
                            'Lainnya' => 'Lainnya',
                        ]),
                    Forms\Components\TextInput::make('harga')
                        ->prefix('Rp ')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->helperText('Harga barang berdasarkan faktur/kuitansi pembelian apabila barang yang bersangkutan berasal dari pembelian. Apabila barang yang bersangkutan berasal dari sumbangan/hadiah supaya diperkirakan dengan harga yang wajar. Pencatatannya dalam rupiah.'),
                    Forms\Components\Select::make('kondisi')
                        ->options([
                            'Baik' => 'Baik',
                            'Kurang Baik' => 'Kurang Baik',
                            'Rusak Berat' => 'Rusak Berat',
                        ]),
                    Forms\Components\FileUpload::make('dokumentasi')
                        ->disk('minio')
                        ->directory('peralatan-mesin')
                        ->multiple()
                        ->maxFiles(10)
                        ->maxSize(10240) // 10MB per file
                        ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->openable()
                        ->downloadable()
                        ->preserveFilenames()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('keterangan')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('user')
                        ->label('Operator')
                        ->default(fn() => auth()->user()->nama)
                        ->maxLength(255),
                ])->columns(),
                
                // Section khusus Kendaraan Dinas
                Forms\Components\Section::make('Data Kendaraan Dinas')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_pabrik')
                            ->label('Nomor Pabrik')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nomor_rangka')
                            ->label('Nomor Rangka')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nomor_mesin')
                            ->label('Nomor Mesin')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nomor_polisi')
                            ->label('Nomor Polisi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bpkb')
                            ->label('BPKB')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($get) => $get('kategori') === 'Kendaraan Dinas')
                    ->columns(),
                
                // Section khusus Pompa
                Forms\Components\Section::make('Data Pompa')
                    ->schema([
                        Forms\Components\TextInput::make('kapasitas_listrik_kwh')
                            ->label('Kapasitas Listrik (KWH)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('kapasitas_air')
                            ->label('Kapasitas Air')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('head_tekanan')
                            ->label('Head (Tekanan)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('merk_panel_pompa')
                            ->label('Merk Panel Pompa')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tipe_panel_pompa')
                            ->label('Tipe Panel Pompa')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Checkbox::make('rtu')
                            ->label('RTU')
                            ->helperText('Centang jika RTU tersedia'),
                    ])
                    ->visible(fn($get) => $get('kategori') === 'Pompa')
                    ->columns(),
                
                // Section Kelistrikan (untuk Peralatan dan Pompa)
                Forms\Components\Section::make('Data Kelistrikan')
                    ->schema([
                        Forms\Components\TextInput::make('kapasitas_listrik_va')
                            ->label('Kapasitas Listrik (VA)')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slo')
                            ->label('SLO (Sertifikat Layak Operasi)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('jil')
                            ->label('JIL (Jaminan Instalasi Listrik)')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('genset')
                            ->label('Genset')
                            ->columnSpanFull(),
                        Forms\Components\Checkbox::make('panel_listrik')
                            ->label('Panel Listrik')
                            ->helperText('Centang jika panel listrik tersedia'),
                        Forms\Components\Checkbox::make('rumah_panel')
                            ->label('Rumah Panel')
                            ->helperText('Centang jika rumah panel tersedia'),
                    ])
                    ->visible(fn($get) => in_array($get('kategori'), ['Pompa']))
                    ->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->description(fn($record) => $record->kode_lokasi),
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->searchable(),
                Tables\Columns\TextColumn::make('merek')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bahan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_pembelian')
                    ->label('Tahun Pembelian')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pengadaan')
                    ->date('j F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('asal_usul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->prefix('Rp '),
                Tables\Columns\TextColumn::make('nilai_buku')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->prefix('Rp ')
                    ->label('Nilai Buku')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('kondisi'),
                Tables\Columns\TextColumn::make('dokumentasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\SelectFilter::make('kategori')
                //     ->label('Kategori')
                //     ->options([
                //         'Peralatan' => 'Peralatan',
                //         'Kendaraan Dinas' => 'Kendaraan Dinas',
                //         'Pompa' => 'Pompa',
                //     ]),
                Tables\Filters\SelectFilter::make('tahun_pembelian')
                    ->label('Tahun Pembelian')
                    ->options(function () {
                        $years = [];
                        $currentYear = date('Y');
                        for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),
                Tables\Filters\SelectFilter::make('tahun')
                    ->label('Tahun Penempatan')
                    ->options(function () {
                        $years = [];
                        $currentYear = date('Y');
                        for ($i = $currentYear; $i >= $currentYear - 4; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),
                Tables\Filters\SelectFilter::make('ruangan_id')
                    ->label('Ruangan')
                    ->multiple()
                    ->options(\App\Models\Lokasi\Ruangan::pluck('nama', 'id')),
                Tables\Filters\SelectFilter::make('bidang_id')
                    ->label('Bidang')
                    ->multiple()
                    ->options(\App\Models\Lokasi\Bidang::pluck('bidang', 'id'))
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['values'],
                            fn($query, $values) => $query->whereHas('subBidang', function ($query) use ($values) {
                                $query->whereIn('id_bidang', $values);
                            })
                        );
                    }),
                Tables\Filters\SelectFilter::make('kondisi')
                    ->label('Kondisi')
                    ->options([
                        'Baik' => 'Baik',
                        'Kurang Baik' => 'Kurang Baik',
                        'Rusak Berat' => 'Rusak Berat',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak_massal_baru')
                    ->label('Cetak Label Massal')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (Tables\Actions\Action $action) {
                        $query = $action->getLivewire()->getFilteredTableQuery();
                        $count = $query->count();
                        
                        if ($count == 0) {
                            $action->getLivewire()->js("alert('Tidak ada data yang cocok dengan filter saat ini.')");
                            return;
                        }

                        if ($count > 200) {
                            $action->getLivewire()->js("alert('Jumlah data ({$count}) terlalu banyak untuk dicetak sekaligus. Silakan gunakan filter untuk memperkecil jumlah data (maksimal 200).')");
                            return;
                        }

                        $ids = $query->pluck('id')->implode(',');
                        $url = route('assets.bulk-print-labels', [
                            'type' => 'peralatan-mesin',
                            'ids' => $ids
                        ]);
                        $action->getLivewire()->js("window.open('{$url}', '_blank')");
                    })
                    ->tooltip('Cetak semua label aset sesuai filter saat ini (maksimal 200 data).'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('qrcode')
                    ->label('Label')
                    ->icon('heroicon-o-qr-code')
                    ->modalContent(fn($record) => view('filament.modals.qrcode-preview-peralatan-mesin', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('6xl'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('cetak_label_bulk')
                        ->label('Cetak Label')
                        ->icon('heroicon-o-printer')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, Tables\Actions\BulkAction $action) {
                            $ids = $records->pluck('id')->implode(',');
                            $url = route('assets.bulk-print-labels', [
                                'type' => 'peralatan-mesin',
                                'ids' => $ids
                            ]);
                            $action->getLivewire()->js("window.open('{$url}', '_blank')");
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(function ($query) {
                return $query->when(auth()->user()->akses == 'User', function ($query) {
                    $wilayah = ['Mataram', 'Gunungsari', 'Gerung'];
                    $subBidang = auth()->user()->subBidang;
                    foreach ($wilayah as $w) {
                        if (str_contains($subBidang?->sub_bidang, $w)) {
                            return $query->whereHas('subBidang', function($query) use ($w) {
                                $query->where('sub_bidang', 'like', '%' . $w . '%')->orWhereHas('bidang', function($query) {
                                    $query->where('bidang', 'Kehilangan Air');
                                })->orWhere('id_bidang', auth()->user()->bidang_id);
                            });
                        }
                    }
                    return $query->whereIn('sub_bidang_id', SubBidang::where('id_bidang', auth()->user()->bidang_id)->pluck('id'));
                });
            });
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MaintenanceLogsRelationManager::class,
            RelationManagers\MaintenanceSchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeralatanMesin::route('/'),
            'create' => Pages\CreatePeralatanMesin::route('/create'),
            'edit' => Pages\EditPeralatanMesin::route('/{record}/edit'),
            'view' => Pages\ViewPeralatanMesin::route('/{record}'),
        ];
    }
}
