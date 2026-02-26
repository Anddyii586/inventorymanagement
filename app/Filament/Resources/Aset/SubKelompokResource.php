<?php

namespace App\Filament\Resources\Aset;

use App\Filament\Resources\Aset\SubKelompokResource\Pages;
use App\Models\Aset\SubKelompok;
use App\Services\SubKelompokService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubKelompokResource extends Resource
{
    protected static ?string $model = SubKelompok::class;

    protected static ?string $slug = 'aset/sub-kelompok';

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationGroup = 'Kode Aset';

    protected static ?int $navigationSort = 24;

    public static function form(Form $form): Form
    {
        $generateId = fn($set, $get, $record) => $set('id', SubKelompokService::generateId($get('id_kelompok'), $record));

        return $form
            ->schema([
                Forms\Components\Select::make('id_kelompok')
                    ->relationship(name: 'kelompok', titleAttribute: 'kelompok')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated($generateId)
                    ->required(),
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->readOnly()
                    ->helperText('ID diisi otomatis oleh sistem'),
                Forms\Components\TextInput::make('sub_kelompok')
                    ->required()
                    ->maxLength(125),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_kelompok')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelompok')
                    ->formatStateUsing(fn($state): string => $state->kelompok),
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
            'index' => Pages\ManageSubKelompoks::route('/'),
        ];
    }
}
