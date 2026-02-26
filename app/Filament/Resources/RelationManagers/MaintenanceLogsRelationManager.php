<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceLogs';

    public function form(Form $form): Form
    {
        return $form
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
                Forms\Components\TextInput::make('pelaksana')
                    ->label('Vendor/Pelaksana')
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
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Keterangan')
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal_mulai')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_pemeliharaan')
                    ->label('Jenis')
                    ->badge(),
                Tables\Columns\TextColumn::make('biaya')
                    ->label('Biaya')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dalam Proses' => 'warning',
                        'Selesai' => 'success',
                        'Dibatalkan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pelaksana')
                    ->label('Pelaksana')
                    ->limit(20),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
