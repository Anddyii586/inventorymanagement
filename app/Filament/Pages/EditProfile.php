<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    protected static string $view = 'filament.pages.edit-profile';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('user')
                    ->label('Username')
                    ->required()
                    ->maxLength(255)
                    ->disabled(), // Username tidak bisa diubah
                
                Forms\Components\TextInput::make('password')
                    ->label('Password Baru')
                    ->password()
                    ->maxLength(255)
                    ->autocomplete(false)
                    ->dehydrated(fn($state) => !empty($state))
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                    ->helperText('Kosongkan jika tidak ingin mengubah password'),
            ]);
    }
}

