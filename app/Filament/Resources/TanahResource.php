<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Tanah;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use App\Services\KodifikasiService;
use App\Filament\Resources\TanahResource\Pages;
use App\Filament\Resources\RelationManagers;

class TanahResource extends Resource
{
    protected static ?string $model = Tanah::class;

    protected static ?string $slug = 'tanah';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $kodeLokasi = fn($set, $get) => $set('kode_lokasi', KodifikasiService::kodeLokasi($get('wilayah_id'), $get('sub_bidang_id'), $get('unit_id'), $get('tahun')));
        $kodeBarang = fn($set, $get, $record) => $set('id', KodifikasiService::kodeBarang($record, $get('sub_sub_kelompok_id'), $get('tanggal_pengadaan'), Tanah::class));
        return $form
            ->schema([
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
                        ->relationship(name: 'subSubKelompok', modifyQueryUsing: fn($query) => $query->where('id', 'like', '02.%'))
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
                Forms\Components\Section::make('Rincian Tanah')->schema([
                    Forms\Components\TextInput::make('luas')
                        ->numeric()
                        ->suffix('m²')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(','),
                    Forms\Components\DatePicker::make('tanggal_pengadaan')
                        ->live(true)
                        ->afterStateUpdated($kodeBarang),
                    Forms\Components\TextInput::make('letak')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('hak')
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('tanggal_sertifikat'),
                    Forms\Components\TextInput::make('nomor_sertifikat')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('penggunaan')
                        ->maxLength(255),
                    Forms\Components\Select::make('asal_usul')
                        ->label('Asal Usul')
                        ->options([
                            'Pemberian Hak' => 'Pemberian Hak',
                            'Pembelian' => 'Pembelian',
                            'Penyertaan Modal Pemerintah Kabupaten Lombok Barat' => 'Penyertaan Modal Pemerintah Kabupaten Lombok Barat',
                            'Penyertaan Modal Pemerintah Kota Mataram' => 'Penyertaan Modal Pemerintah Kota Mataram',
                            'Hibah' => 'Hibah',
                            'Lainnya' => 'Lainnya',
                        ])
                        ->searchable(),
                    Forms\Components\TextInput::make('jangka_waktu')
                        ->numeric(),
                    Forms\Components\TextInput::make('berakhir')
                        ->maxLength(4),
                    Forms\Components\TextInput::make('harga')
                        ->numeric()
                        ->prefix('Rp ')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(','),
                    Forms\Components\Select::make('kondisi')
                        ->label('Kondisi')
                        ->options([
                            'Baik' => 'Baik',
                            'Tidak Baik' => 'Tidak Baik',
                        ]),
                    Forms\Components\Toggle::make('is_idle')
                        ->label('Idle')
                        ->default(true)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('dokumentasi')
                        ->disk('minio')
                        ->directory('tanah')
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
                    Forms\Components\TextInput::make('user')
                        ->label('Operator')
                        ->maxLength(255)
                        ->default(auth()->user()->nama)
                        ->readOnly()
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
                Tables\Columns\TextColumn::make('luas')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->alignRight()
                    ->suffix(' m²'),
                Tables\Columns\TextColumn::make('tanggal_pengadaan')
                    ->date('j F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('letak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_sertifikat')
                    ->date('j F Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nomor_sertifikat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('penggunaan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('asal_usul')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jangka_waktu')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('berakhir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('harga')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->prefix('Rp '),
                Tables\Columns\TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                            'type' => 'tanah',
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
                    ->modalContent(fn($record) => view('filament.modals.qrcode-preview', [
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
                                'type' => 'tanah',
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
            'index' => Pages\ListTanah::route('/'),
            'create' => Pages\CreateTanah::route('/create'),
            'view' => Pages\ViewTanah::route('/{record}'),
            'edit' => Pages\EditTanah::route('/{record}/edit'),
        ];
    }

    public static function getActions(): array
    {
        return [
            Action::make('qrcode')
                ->label('Label')
                ->icon('heroicon-o-qr-code')
                ->modalContent(fn($record) => view('filament.modals.qrcode-preview', [
                    'record' => $record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalWidth('7xl')
                ->visible(fn($record) => $record !== null),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->akses == 'Administrator';
    }
}
