<?php

namespace App\Filament\Resources\Lokasi;

use App\Filament\Resources\Lokasi\SubBidangResource\Pages;
use App\Models\Lokasi\SubBidang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubBidangResource extends Resource
{
    protected static ?string $model = SubBidang::class;

    protected static ?string $slug = 'lokasi/sub-bidang';

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Kode Lokasi';

    protected static ?int $navigationSort = 14;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->required()
                    ->maxLength(2)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('sub_bidang')
                    ->required()
                    ->maxLength(125),
                Forms\Components\Select::make('id_bidang')
                    ->relationship(name: 'bidang', titleAttribute: 'bidang'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_bidang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bidang.bidang')
                    ->searchable(),
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
            'index' => Pages\ManageSubBidang::route('/'),
        ];
    }
}
