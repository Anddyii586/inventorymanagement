<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceSchedules';

    protected static ?string $title = 'Jadwal Pemeliharaan';

    public function form(Form $form): Form
    {
        return $form
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
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\DatePicker::make('tanggal_terakhir')
                        ->label('Terakhir Dilakukan'),
                    Forms\Components\DatePicker::make('tanggal_berikutnya')
                        ->label('Jadwal Berikutnya')
                        ->required(),
                ]),
                Forms\Components\Toggle::make('is_aktif')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_tugas')
            ->columns([
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
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('logMaintenance')
                    ->label('Catat Pemeliharaan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('biaya')
                            ->label('Biaya')
                            ->numeric()
                            ->prefix('Rp ')
                            ->default(0),
                        Forms\Components\TextInput::make('pelaksana')
                            ->label('Pelaksana')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Keterangan'),
                    ])
                    ->action(function (array $data, \App\Models\MaintenanceSchedule $record): void {
                        // Create log
                        $record->maintenanceable->maintenanceLogs()->create([
                            'tanggal_mulai' => $record->tanggal_berikutnya ?? now(),
                            'tanggal_selesai' => $data['tanggal_selesai'],
                            'jenis_pemeliharaan' => 'Servis Rutin', // Default or based on task name
                            'biaya' => $data['biaya'],
                            'pelaksana' => $data['pelaksana'],
                            'deskripsi' => $data['deskripsi'] ?? $record->nama_tugas,
                            'status' => 'Selesai',
                            'user_id' => auth()->id(),
                        ]);

                        // Update schedule
                        $nextDate = match ($record->frekuensi) {
                            'Harian' => now()->addDay(),
                            'Mingguan' => now()->addWeek(),
                            'Bulanan' => now()->addMonth(),
                            'Tahunan' => now()->addYear(),
                            default => now()->addMonth(),
                        };

                        $record->update([
                            'tanggal_terakhir' => $data['tanggal_selesai'],
                            'tanggal_berikutnya' => $nextDate,
                        ]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
