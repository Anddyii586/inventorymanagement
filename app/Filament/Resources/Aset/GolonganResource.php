<?php

namespace App\Filament\Resources\Aset;

use App\Filament\Resources\Aset\GolonganResource\Pages;
use App\Models\Aset\Golongan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GolonganResource extends Resource
{
    protected static ?string $model = Golongan::class;

    protected static ?string $slug = 'aset/golongan';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Kode Aset';

    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->required()
                    ->maxLength(2)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('golongan')
                    ->required()
                    ->maxLength(125),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('golongan')
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
            'index' => Pages\ManageGolongan::route('/'),
        ];
    }
}
