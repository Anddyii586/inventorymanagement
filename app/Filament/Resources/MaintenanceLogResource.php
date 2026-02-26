<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceLogResource\Pages;
use App\Filament\Resources\MaintenanceLogResource\RelationManagers;
use App\Models\MaintenanceLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Pemeliharaan Aset';
    
    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Log Pemeliharaan';

    protected static ?string $pluralLabel = 'Log Pemeliharaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Aset')
                    ->schema([
                        Forms\Components\MorphToSelect::make('maintenanceable')
                            ->label('Pilih Aset')
                            ->types([
                                Forms\Components\MorphToSelect\Type::make(\App\Models\PeralatanMesin::class)
                                    ->titleAttribute('nama_barang')
                                    ->label('Peralatan & Mesin'),
                                Forms\Components\MorphToSelect\Type::make(\App\Models\Tanah::class)
                                    ->titleAttribute('id')
                                    ->label('Tanah'),
                                Forms\Components\MorphToSelect\Type::make(\App\Models\GedungBangunan::class)
                                    ->titleAttribute('id')
                                    ->label('Gedung & Bangunan'),
                                Forms\Components\MorphToSelect\Type::make(\App\Models\Jaringan::class)
                                    ->titleAttribute('jenis_jaringan')
                                    ->label('Jalan, Jaringan & Irigasi'),
                                Forms\Components\MorphToSelect\Type::make(\App\Models\AsetTetapLainnya::class)
                                    ->titleAttribute('judul_buku')
                                    ->label('Aset Tetap Lainnya'),
                                Forms\Components\MorphToSelect\Type::make(\App\Models\KonstruksiDalamPengerjaan::class)
                                    ->titleAttribute('id')
                                    ->label('Konstruksi Dalam Pengerjaan'),
                            ])
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Detail Pemeliharaan')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('tanggal_mulai')
                                ->label('Tanggal Mulai')
                                ->default(now())
                                ->required(),
                            Forms\Components\DatePicker::make('tanggal_selesai')
                                ->label('Tanggal Selesai'),
                        ]),
                        Forms\Components\Select::make('jenis_pemeliharaan')
                            ->label('Jenis Pemeliharaan')
                            ->options([
                                'Perbaikan' => 'Perbaikan',
                                'Servis Rutin' => 'Servis Rutin',
                                'Inspeksi' => 'Inspeksi',
                                'Penggantian Part' => 'Penggantian Part',
                            ])
                            ->required(),
                        Forms\Components\Select::make('vendor_id')
                            ->relationship('vendor', 'nama')
                            ->label('Vendor (Pihak Ketiga)')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama')->required(),
                                Forms\Components\TextInput::make('kontak'),
                            ]),
                        Forms\Components\TextInput::make('pelaksana')
                            ->label('Pelaksana (Manual)')
                            ->placeholder('Opsional jika tidak pilih vendor')
                            ->helperText('Isi jika dikerjakan sendiri / internal')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('biaya')
                            ->label('Biaya')
                            ->numeric()
                            ->prefix('Rp ')
                            ->default(0),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Dalam Proses' => 'Dalam Proses',
                                'Selesai' => 'Selesai',
                                'Dibatalkan' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('Selesai'),
                        Forms\Components\Select::make('maintenance_request_id')
                            ->label('Dari Tiket Permintaan')
                            ->relationship('maintenanceRequest', 'ticket_number')
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih jika pemeliharaan ini berasal dari tiket perbaikan'),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Keterangan / Detail Pekerjaan')
                            ->placeholder('Beri rincian apa yang dikerjakan...')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('maintenanceable_id')
                    ->label('ID Aset')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asset_name')
                    ->label('Nama Aset')
                    ->getStateUsing(function ($record) {
                        $asset = $record->maintenanceable;
                        if (!$asset) return '-';
                        return $asset->nama_barang 
                            ?? $asset->jenis_jaringan 
                            ?? $asset->judul_buku 
                            ?? '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHasMorph(
                            'maintenanceable',
                            ['App\Models\PeralatanMesin', 'App\Models\Jaringan', 'App\Models\AsetTetapLainnya', 'App\Models\Tanah', 'App\Models\GedungBangunan', 'App\Models\KonstruksiDalamPengerjaan'],
                            function (Builder $query) use ($search) {
                                $query->where('nama_barang', 'like', "%{$search}%")
                                    ->orWhere('jenis_jaringan', 'like', "%{$search}%")
                                    ->orWhere('judul_buku', 'like', "%{$search}%")
                                    ->orWhere('id', 'like', "%{$search}%");
                            }
                        );
                    })
                    ->limit(30),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_pemeliharaan')
                    ->label('Jenis')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('biaya')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pelaksana')
                    ->label('Pelaksana')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dalam Proses' => 'warning',
                        'Selesai' => 'success',
                        'Dibatalkan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.nama')
                    ->label('Inputer')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_pemeliharaan')
                    ->options([
                        'Perbaikan' => 'Perbaikan',
                        'Servis Rutin' => 'Servis Rutin',
                        'Inspeksi' => 'Inspeksi',
                        'Penggantian Part' => 'Penggantian Part',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Dalam Proses' => 'Dalam Proses',
                        'Selesai' => 'Selesai',
                        'Dibatalkan' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceLogs::route('/'),
            'create' => Pages\CreateMaintenanceLog::route('/create'),
            'edit' => Pages\EditMaintenanceLog::route('/{record}/edit'),
        ];
    }
}
