<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Jaringan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\KodifikasiService;
use App\Filament\Resources\JaringanResource\Pages;
use App\Filament\Resources\RelationManagers;

class JaringanResource extends Resource
{
    protected static ?string $model = Jaringan::class;

    protected static ?string $slug = 'jaringan';

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?int $navigationSort = 5;

    protected static ?string $label = 'Jalan, Jaringan & Irigasi';

    public static function form(Form $form): Form
    {
        $kodeLokasi = fn($set, $get) => $set('kode_lokasi', KodifikasiService::kodeLokasi($get('wilayah_id'), $get('sub_bidang_id'), $get('unit_id'), $get('tahun')));
        $kodeBarang = fn($set, $get, $record) => $set('id', KodifikasiService::kodeBarang($record, $get('sub_sub_kelompok_id'), $get('tanggal_pengadaan'), Jaringan::class));
        return $form->schema([
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
                            ->pluck('bidang', 'id')
                    )
                    ->default(function ($record) {
                        if (!$record)
                            return null;
                        $subBidang = \App\Models\Lokasi\SubBidang::where('id', $record->sub_bidang_id)->first();
                        return $subBidang ? $subBidang->id_bidang : null;
                    })
                    ->live()
                    ->searchable()
                    ->preload()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if (!$record)
                            return;
                        $subBidang = \App\Models\Lokasi\SubBidang::where('id', $record->sub_bidang_id)->first();
                        if ($subBidang) {
                            $component->state($subBidang->id_bidang);
                        }
                    })
                    ->dehydrated(false),
                Forms\Components\Select::make('sub_bidang_id')
                    ->relationship(
                        name: 'subBidang',
                        titleAttribute: 'sub_bidang',
                        modifyQueryUsing: fn($query, $get) => $query->when(
                            $get('bidang_id'),
                            fn($query, $bidang_id) => $query->where('id_bidang', $bidang_id)
                        )
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
                    ->afterStateUpdated($kodeLokasi)
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('tahun')
                    ->label('Tahun penempatan')
                    ->live(true)
                    ->afterStateUpdated($kodeLokasi)
                    ->numeric()
                    ->minValue(1901)
                    ->maxValue(date('Y')),
                Forms\Components\TextInput::make('kode_lokasi')
                    ->readOnly()
                    ->helperText('Kode Lokasi diisi otomatis oleh sistem')
                    ->columnSpanFull(),
            ])->columns(),
            Forms\Components\Section::make('Kode Aset')->schema([
                Forms\Components\Select::make('sub_sub_kelompok_id')
                    ->live()
                    ->afterStateUpdated($kodeBarang)
                    ->relationship(name: 'subSubKelompok', modifyQueryUsing: fn($query) => $query->where('id', 'like', '05.%'))
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->id} {$record->sub_sub_kelompok}")
                    ->searchable(['id', 'sub_sub_kelompok'])
                    ->preload(),
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->readOnly()
                    ->required()
                    ->validationAttribute('ID')
                    ->helperText('Kode Barang diisi otomatis oleh sistem'),
            ])->columns(),
            Forms\Components\Section::make('Rincian Jalan, Jaringan & Irigasi')->schema([
                Forms\Components\TextInput::make('jenis_jaringan')
                    ->helperText('Jenis jaringan yang dikembangkan ataupun dilakukan perbaikannya. Contoh: Jaringan Transmisi, Jaringan Distribusi, Jaringan Pipa Dinas dan lain-lain sejenisnya.')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('tanggal_pengadaan'),
                Forms\Components\DatePicker::make('tanggal_pemasangan'),
                Forms\Components\TextInput::make('jenis_pipa')
                    ->helperText('Jenis pipa yang digunakan. Contoh: Pipa PVC, Pipa ACP, Pipa GI, dan sebagainya.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('diameter')
                    ->helperText('Diameter pipa yang digunakan dalam satuan inch.')
                    ->suffix('inch')
                    ->maxLength(255),
                Forms\Components\TextInput::make('panjang')
                    ->helperText('Panjang pipa yang digunakan dalam satuan meter.')
                    ->suffix('m')
                    ->maxLength(255),
                Forms\Components\TextInput::make('letak')
                    ->helperText('Letak/alamat lengkap lokasi dari jaringan perpipaan. Contoh: Jl. Pendidikan No. 39, Mataram.')
                    ->maxLength(255),
                Forms\Components\Select::make('status_tanah')
                    ->options([
                        'Tanah Milik Pemerintah Daerah' => 'Tanah Milik Pemerintah Daerah',
                        'Tanah Negara' => 'Tanah Negara (Tanah yang dikuasai langsung oleh Negara)',
                        'Tanah Hak Ulayat' => 'Tanah Hak Ulayat (Tanah masyarakat Hukum Adat)',
                        'Tanah Hak' => 'Tanah Hak (Tanah kepunyaan perorangan atau Badan Hukum), Hak Guna Bangunan, Hak Pakai atau Hak Pengelolaan',
                    ]),
                Forms\Components\TextInput::make('nomor_sertifikat_tanah')
                    ->maxLength(255),
                Forms\Components\TextInput::make('asal_usul')
                    ->helperText('Asal perolehan dari tanah. Contoh: dibeli, hibah, dan lain-lain. Dalam hal jalan, irigasi dan jaringan yang dibiayai dari beberapa sumber anggaran, dicatat sebagai milik komponen pemilikan pokok, misalnya jalan, irigasi dan jaringan Pemda dibantu dari anggaran Pusat maka statusnya tetap dicatat sebagai milik Pemda.')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('harga')
                    ->helperText('Harga yang sebenarnya untuk kegiatan pemasangan jaringan dalam satuan rupiah. Apabila nilai jalan, irigasi dan jaringan tidak dapat diketahui berdasarkan dokumen yang ada, maka perkirakanlah nilai jalan, irigasi dan jaringan berdasarkan harga yang berlaku dilingkungan tersebut pada waktu pencatatan.')
                    ->prefix('Rp')
                    ->numeric()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sumber_dana')
                    ->helperText('Sumber dana yang digunakan dalam pengembangan jaringan. Contoh: SPAM Strategis, PDAM, Hibah.')
                    ->maxLength(255),
                Forms\Components\Select::make('kondisi')
                    ->options([
                        'Baik' => 'Baik',
                        'Kurang Baik' => 'Kurang Baik',
                        'Rusak Berat' => 'Rusak Berat',
                    ]),
                Forms\Components\FileUpload::make('dokumentasi')
                    ->disk('minio')
                    ->directory('jaringan')
                    ->multiple()
                    ->maxFiles(10)
                    ->maxSize(10240) // 10MB per file
                    ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->openable()
                    ->downloadable()
                    ->preserveFilenames()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('keterangan')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])->columns(),
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
                Tables\Columns\TextColumn::make('jenis_jaringan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pengadaan')
                    ->date('j F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pemasangan')
                    ->date('j F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_pipa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('diameter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('panjang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('letak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_tanah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_sertifikat_tanah')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('sumber_dana')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kondisi'),
                Tables\Columns\TextColumn::make('dokumentasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                            'type' => 'jaringan',
                            'ids' => $ids
                        ]);
                        $action->getLivewire()->js("window.open('{$url}', '_blank')");
                    })
                    ->tooltip('Cetak semua label aset sesuai filter saat ini (maksimal 200 data).'),
            ])
            ->filters([
                //

            ])
            ->actions([
                Tables\Actions\Action::make('qrcode')
                    ->label('Label')
                    ->icon('heroicon-o-qr-code')
                    ->modalContent(fn($record) => view('filament.modals.qrcode-preview-jaringan', [
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
                                'type' => 'jaringan',
                                'ids' => $ids
                            ]);
                            $action->getLivewire()->js("window.open('{$url}', '_blank')");
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListJaringan::route('/'),
            'create' => Pages\CreateJaringan::route('/create'),
            'view' => Pages\ViewJaringan::route('/{record}'),
            'edit' => Pages\EditJaringan::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->akses == 'Administrator';
    }
}
