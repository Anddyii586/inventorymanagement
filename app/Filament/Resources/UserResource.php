<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Pegawai;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Services\HrdConnectionService;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $navigationGroup = 'Pengaturan';

    public static function form(Form $form): Form
    {
        // Safely check HRD availability with fallback to false
        $hrdAvailable = false;
        try {
            // Use cached version but with shorter cache time for more responsive updates
            $hrdAvailable = HrdConnectionService::isAvailableCached(1); // Cache for 1 minute only
        } catch (\Throwable $e) {
            // If service check fails, assume HRD is not available
            $hrdAvailable = false;
        }
        
        return $form
            ->schema([
                Forms\Components\Select::make('pegawai_id')
                    ->label('Pegawai')
                    ->options(function () use ($hrdAvailable) {
                        // Double check availability before attempting query
                        if (!$hrdAvailable) {
                            return [];
                        }
                        
                        // Quick socket check before attempting database query
                        // Support test mode via environment variables
                        $config = config('database.connections.hrd');
                        if (env('HRD_TEST_HOST')) {
                            $config['host'] = env('HRD_TEST_HOST');
                        }
                        if (env('HRD_TEST_PORT')) {
                            $config['port'] = env('HRD_TEST_PORT');
                        }
                        
                        if ($config && isset($config['host']) && isset($config['port'])) {
                            $socketCheck = @fsockopen($config['host'], $config['port'], $errno, $errstr, 0.5);
                            if (!$socketCheck) {
                                // Clear cache and return empty
                                Cache::forget('hrd_connection_available');
                                return [];
                            }
                            fclose($socketCheck);
                        }
                        
                        try {
                            return Pegawai::all()->pluck('nama', 'id');
                        } catch (\Exception $e) {
                            // Clear cache on error
                            Cache::forget('hrd_connection_available');
                            return [];
                        } catch (\Throwable $e) {
                            // Clear cache on error
                            Cache::forget('hrd_connection_available');
                            return [];
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->disabled(!$hrdAvailable)
                    ->helperText(
                        !$hrdAvailable 
                            ? 'Fitur ini dinonaktifkan karena tidak dapat terhubung ke database HRD. Fitur akan aktif otomatis saat koneksi tersedia.'
                            : null
                    )
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $record) use ($hrdAvailable) {
                        if (!$hrdAvailable) {
                            return;
                        }
                        try {
                        $pegawai = Pegawai::find($state);
                        if ($pegawai && $record?->id === null) {
                            $set('nama', $pegawai->nama);
                            $set('user', $pegawai->nik);
                            $set('password', $pegawai->nik);
                            }
                        } catch (\Exception $e) {
                            // Silently fail if HRD connection fails during state update
                        } catch (\Throwable $e) {
                            // Silently fail if HRD connection fails during state update
                        }
                    }),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user')
                    ->label('Username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->maxLength(255)
                    ->autocomplete(false)
                    ->dehydrated(fn($state, $record) => !empty($state) || $record === null)
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                    ->required(fn($record) => $record?->id === null)
                    ->helperText(fn($record) => $record?->id ? 'Kosongkan jika tidak ingin mengubah password' : null),
                Forms\Components\Select::make('bidang_id')
                    ->label('Bidang')
                    ->options(
                        \App\Models\Lokasi\Bidang::query()
                            ->pluck('bidang', 'id')
                    )
                    ->default(function ($record) {
                        if (!$record)
                            return null;
                        $subBidang = \App\Models\Lokasi\SubBidang::where('id', $record->sub_bidang_id)->first();
                        return $subBidang ? $subBidang->id_bidang : null;
                    })
                    ->live()
                    ->searchable()
                    ->preload()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if (!$record)
                            return;
                        $subBidang = \App\Models\Lokasi\SubBidang::where('id', $record->sub_bidang_id)->first();
                        if ($subBidang) {
                            $component->state($subBidang->id_bidang);
                        }
                    })
                    ->afterStateUpdated(function ($set) {
                        $set('sub_bidang_id', null);
                    }),
                Forms\Components\Select::make('sub_bidang_id')
                    ->relationship(
                        name: 'subBidang',
                        titleAttribute: 'sub_bidang',
                        modifyQueryUsing: fn($query, $get) => $query->when(
                            $get('bidang_id'),
                            fn($query, $bidang_id) => $query->where('id_bidang', $bidang_id)
                        )
                    )
                    ->default(fn($record) => $record?->sub_bidang_id)
                    ->live()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('akses')
                    ->options([
                        'Administrator' => 'Administrator',
                        'User' => 'User',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bidang')
                    ->formatStateUsing(fn($state) => $state->bidang)
                    ->sortable(),
                Tables\Columns\TextColumn::make('subBidang.sub_bidang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('akses')
                    ->label('Akses')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->akses == 'Administrator';
    }
}
