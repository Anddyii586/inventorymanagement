<?php

namespace App\Filament\Resources\Aset;

use App\Filament\Resources\Aset\KelompokResource\Pages;
use App\Models\Aset\Kelompok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KelompokResource extends Resource
{
    protected static ?string $model = Kelompok::class;

    protected static ?string $slug = 'aset/kelompok';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Kode Aset';

    protected static ?int $navigationSort = 23;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->required()
                    ->maxLength(2)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('kelompok')
                    ->required()
                    ->maxLength(125),
                Forms\Components\Select::make('id_bidang')
                    ->relationship(name: 'bidang', titleAttribute: 'bidang')
                    ->searchable()
                    ->preload(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelompok')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bidang')
                    ->formatStateUsing(fn($state): string => $state->bidang),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKelompoks::route('/'),
        ];
    }
}
