<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Filament\Resources\MaintenanceRequestResource\RelationManagers;
use App\Models\MaintenanceRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Pemeliharaan Aset';

    protected static ?int $navigationSort = 4;
    
    protected static ?string $label = 'Tiket Perbaikan';
    
    protected static ?string $pluralLabel = 'Tiket Perbaikan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tiket')->schema([
                    Forms\Components\TextInput::make('ticket_number')
                        ->label('Nomor Tiket')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn ($record) => $record !== null),
                    Forms\Components\Hidden::make('user_id')
                        ->default(auth()->id()),
                    Forms\Components\DatePicker::make('tanggal_laporan')
                        ->label('Tanggal Laporan')
                        ->default(now())
                        ->required(),
                    Forms\Components\Select::make('prioritas')
                        ->label('Prioritas')
                        ->options([
                            'Rendah' => 'Rendah',
                            'Sedang' => 'Sedang',
                            'Tinggi' => 'Tinggi',
                            'Darurat' => 'Darurat',
                        ])
                        ->default('Sedang')
                        ->required(),
                    Forms\Components\MorphToSelect::make('maintenanceable')
                        ->label('Aset yang Bermasalah')
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
                ])->columns(2),
                
                Forms\Components\Section::make('Detail Permasalahan')->schema([
                    Forms\Components\TextInput::make('judul')
                        ->label('Judul / Subjek')
                        ->placeholder('Contoh: AC Bocor, Mesin Bunyi Kasar')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('deskripsi')
                        ->label('Deskripsi Masalah')
                        ->placeholder('Jelaskan masalah secara detail...')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('bukti_foto')
                        ->label('Foto Bukti (Opsional)')
                        ->image()
                        ->directory('maintenance-requests')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Section::make('Status Tiket')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'Pending' => 'Pending',
                                'Disetujui' => 'Disetujui',
                                'Ditolak' => 'Ditolak',
                                'Selesai' => 'Selesai',
                            ])
                            ->default('Pending')
                            ->required(),
                    ])
                    ->visible(fn () => auth()->user()->akses !== 'User'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_laporan')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nama')
                    ->label('Pelapor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('maintenanceable_type')
                    ->label('Jenis Aset')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('prioritas')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Rendah' => 'gray',
                        'Sedang' => 'info',
                        'Tinggi' => 'warning',
                        'Darurat' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'gray',
                        'Disetujui' => 'info',
                        'Ditolak' => 'danger',
                        'Selesai' => 'success',
                    }),
                Tables\Columns\ImageColumn::make('bukti_foto')
                    ->label('Foto')
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListMaintenanceRequests::route('/'),
            'create' => Pages\CreateMaintenanceRequest::route('/create'),
            'edit' => Pages\EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }
}
