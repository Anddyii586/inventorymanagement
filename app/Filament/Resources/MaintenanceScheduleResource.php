<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceScheduleResource\Pages;
use App\Filament\Resources\MaintenanceScheduleResource\RelationManagers;
use App\Models\MaintenanceSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceScheduleResource extends Resource
{
    protected static ?string $model = MaintenanceSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Pemeliharaan Aset';
    
    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Jadwal Pemeliharaan';

    protected static ?string $pluralLabel = 'Jadwal Pemeliharaan';

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
                Forms\Components\Section::make('Detail Penjadwalan')
                    ->schema([
                        Forms\Components\TextInput::make('nama_tugas')
                            ->label('Nama Tugas / Aktivitas')
                            ->placeholder('Contoh: Ganti Oli Mesin, Cek Kebocoran')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('frekuensi')
                            ->label('Frekuensi')
                            ->options([
                                'Harian' => 'Harian',
                                'Mingguan' => 'Mingguan',
                                'Bulanan' => 'Bulanan',
                                'Tahunan' => 'Tahunan',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_terakhir')
                            ->label('Terakhir Dilakukan'),
                        Forms\Components\DatePicker::make('tanggal_berikutnya')
                            ->label('Jadwal Berikutnya')
                            ->required(),
                        Forms\Components\Toggle::make('is_aktif')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                        Forms\Components\Toggle::make('enable_notifikasi')
                            ->label('Aktifkan Notifikasi')
                            ->helperText('Kirim pengingat otomatis untuk jadwal pemeliharaan ini')
                            ->default(true)
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('notifikasi_hari_sebelumnya')
                            ->label('Kirim Notifikasi (Hari Sebelumnya)')
                            ->helperText('Berapa hari sebelum tanggal terjadwal untuk mengirim pengingat')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(30)
                            ->suffix('hari')
                            ->visible(fn ($get) => $get('enable_notifikasi'))
                            ->required(fn ($get) => $get('enable_notifikasi')),
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
                Tables\Columns\TextColumn::make('nama_tugas')
                    ->label('Aktivitas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('frekuensi')
                    ->label('Frekuensi')
                    ->badge(),
                Tables\Columns\TextColumn::make('tanggal_berikutnya')
                    ->label('Jadwal Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state < now() ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('frekuensi')
                    ->options([
                        'Harian' => 'Harian',
                        'Mingguan' => 'Mingguan',
                        'Bulanan' => 'Bulanan',
                        'Tahunan' => 'Tahunan',
                    ]),
                Tables\Filters\TernaryFilter::make('is_aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMaintenanceSchedules::route('/'),
            'create' => Pages\CreateMaintenanceSchedule::route('/create'),
            'edit' => Pages\EditMaintenanceSchedule::route('/{record}/edit'),
        ];
    }
}
