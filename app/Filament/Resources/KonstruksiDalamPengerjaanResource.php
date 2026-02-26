<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Services\KodifikasiService;
use Filament\Resources\Resource;
use App\Models\KonstruksiDalamPengerjaan;
use App\Filament\Resources\KonstruksiDalamPengerjaanResource\Pages;
use App\Filament\Resources\RelationManagers;

class KonstruksiDalamPengerjaanResource extends Resource
{
    protected static ?string $model = KonstruksiDalamPengerjaan::class;

    protected static ?string $slug = 'konstruksi-dalam-pengerjaan';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?int $navigationSort = 7;

    protected static ?string $label = 'Konstruksi Dalam Pengerjaan';

    public static function form(Form $form): Form
    {
        $kodeLokasi = fn($set, $get) => $set('kode_lokasi', KodifikasiService::kodeLokasi($get('wilayah_id'), $get('sub_bidang_id'), $get('unit_id'), $get('tahun')));
        $kodeBarang = fn($set, $get, $record) => $set('id', KodifikasiService::kodeBarang($record, $get('sub_sub_kelompok_id'), $get('tanggal_pengadaan'), KonstruksiDalamPengerjaan::class));

        return $form->schema([
            Forms\Components\Section::make('Kode Lokasi')->schema([
                Forms\Components\Select::make('wilayah_id')
                    ->relationship(name: 'wilayah', titleAttribute: 'wilayah')
                    ->live()
                    ->afterStateUpdated($kodeLokasi)
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
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
                        )
                    )
                    ->default(fn($record) => $record?->sub_bidang_id)
                    ->live()
                    ->afterStateUpdated($kodeLokasi)
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('unit_id')
                    ->relationship(name: 'unit', titleAttribute: 'unit')
                    ->live()
                    ->afterStateUpdated($kodeLokasi)
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('tahun')
                    ->label('Tahun penempatan')
                    ->live()
                    ->afterStateUpdated($kodeLokasi)
                    ->numeric()
                    ->minValue(1901)
                    ->maxValue(date('Y')),
                Forms\Components\TextInput::make('kode_lokasi')
                    ->readOnly()
                    ->helperText('Kode Lokasi diisi otomatis oleh sistem')
                    ->columnSpanFull(),
            ])->columns(),

            Forms\Components\Section::make('Kode Barang')->schema([
                Forms\Components\Select::make('sub_sub_kelompok_id')
                    ->live()
                    ->afterStateUpdated($kodeBarang)
                    ->relationship(
                        name: 'subSubKelompok',
                        modifyQueryUsing: fn($query) => $query->where('id', 'like', '06.%')
                    )
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

            Forms\Components\Section::make('Rincian Konstruksi')->schema([
                Forms\Components\Select::make('bangunan')
                    ->label('Tipe Bangunan')
                    ->options([
                        'Permanen' => 'Permanen',
                        'Semi Permanen' => 'Semi Permanen',
                        'Darurat' => 'Darurat'
                    ]),
                Forms\Components\Toggle::make('bertingkat')
                    ->label('Bertingkat'),
                Forms\Components\Toggle::make('beton')
                    ->label('Beton'),
                Forms\Components\TextInput::make('volume')
                    ->maxLength(255),
                Forms\Components\TextInput::make('satuan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('lokasi')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_dokumen'),
                Forms\Components\TextInput::make('nomor_dokumen')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_pengadaan')
                    ->label('Tanggal Pengadaan')
                    ->required(),
                Forms\Components\TextInput::make('status_tanah')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nomor_sertifikat')
                    ->maxLength(255),
                Forms\Components\TextInput::make('asal_usul')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nilai_kontrak')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\FileUpload::make('dokumentasi')
                    ->disk('minio')
                    ->directory('konstruksi-dalam-pengerjaan')
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
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Kode Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bangunan')
                    ->searchable(),
                Tables\Columns\IconColumn::make('bertingkat')
                    ->boolean(),
                Tables\Columns\IconColumn::make('beton')
                    ->boolean(),
                Tables\Columns\TextColumn::make('volume')
                    ->searchable(),
                Tables\Columns\TextColumn::make('satuan'),
                Tables\Columns\TextColumn::make('lokasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nilai_kontrak')
                    ->money('idr'),
                Tables\Columns\TextColumn::make('tanggal_pengad')
                    ->label('Tanggal Pengadaan')
                    ->date(),
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
                            'type' => 'konstruksi-dalam-pengerjaan',
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
                    ->modalContent(fn($record) => view('filament.modals.qrcode-preview-konstruksi-dalam-pengerjaan', [
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
                                'type' => 'konstruksi-dalam-pengerjaan',
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
            'index' => Pages\ListKonstruksiDalamPengerjaan::route('/'),
            'create' => Pages\CreateKonstruksiDalamPengerjaan::route('/create'),
            'view' => Pages\ViewKonstruksiDalamPengerjaan::route('/{record}'),
            'edit' => Pages\EditKonstruksiDalamPengerjaan::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->akses == 'Administrator';
    }
}