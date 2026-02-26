<?php

namespace App\Filament\Resources\PeralatanMesinResource\Pages;

use App\Filament\Resources\PeralatanMesinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeralatanMesin extends EditRecord
{
    protected static string $resource = PeralatanMesinResource::class;

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
        return 'Data peralatan dan mesin berhasil diperbarui';
    }
}
