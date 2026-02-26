<?php

namespace App\Filament\Resources\TanahResource\Pages;

use App\Filament\Resources\TanahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTanah extends EditRecord
{
    protected static string $resource = TanahResource::class;

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
        return 'Data tanah berhasil diperbarui';
    }
}
