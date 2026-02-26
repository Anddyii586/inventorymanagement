<?php

namespace App\Filament\Resources\GedungBangunanResource\Pages;

use App\Filament\Resources\GedungBangunanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGedungBangunan extends EditRecord
{
    protected static string $resource = GedungBangunanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data gedung dan bangunan berhasil diperbarui';
    }
}
