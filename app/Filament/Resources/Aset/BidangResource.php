<?php

namespace App\Filament\Resources\Aset;

use App\Filament\Resources\Aset\BidangResource\Pages;
use App\Models\Aset\Bidang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BidangResource extends Resource
{
    protected static ?string $model = Bidang::class;

    protected static ?string $slug = 'aset/bidang';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Kode Aset';

    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->required()
                    ->maxLength(2)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('bidang')
                    ->required()
                    ->maxLength(125),
                Forms\Components\Select::make('id_golongan')
                    ->relationship(name: 'golongan', titleAttribute: 'golongan')
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
                Tables\Columns\TextColumn::make('bidang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('golongan.golongan')
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
            'index' => Pages\ManageBidang::route('/'),
        ];
    }
}
