<?php

namespace App\Filament\Resources\Aset;

use App\Filament\Resources\Aset\SubSubKelompokResource\Pages;
use App\Filament\Resources\Aset\SubSubKelompokResource\RelationManagers;
use App\Models\Aset\SubSubKelompok;
use App\Services\SubSubKelompokService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubSubKelompokResource extends Resource
{
    protected static ?string $model = SubSubKelompok::class;

    protected static ?string $slug = 'aset/sub-sub-kelompok';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Kode Aset';

    protected static ?int $navigationSort = 25;

    public static function form(Form $form): Form
    {
        $generateId = fn($set, $get, $record) => $set('id', SubSubKelompokService::generateId($get('id_sub_kelompok'), $record));

        return $form
            ->schema([
                Forms\Components\Select::make('id_sub_kelompok')
                    ->relationship(name: 'subKelompok', titleAttribute: 'sub_kelompok')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated($generateId)
                    ->required(),
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->readOnly()
                    ->helperText('ID diisi otomatis oleh sistem'),
                Forms\Components\TextInput::make('sub_sub_kelompok')
                    ->required()
                    ->maxLength(125),
                Forms\Components\TextInput::make('umur_ekonomis')
                    ->numeric()
                    ->suffix('Tahun')
                    ->label('Umur Ekonomis')
                    ->helperText('Masa manfaat aset dalam tahun untuk perhitungan penyusutan'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_sub_kelompok')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subKelompok')
                    ->formatStateUsing(fn($state): string => $state->sub_kelompok),
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
            'index' => Pages\ManageSubSubKelompoks::route('/'),
        ];
    }
}
