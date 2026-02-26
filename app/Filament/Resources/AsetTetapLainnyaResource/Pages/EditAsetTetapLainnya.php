<?php

namespace App\Filament\Resources\AsetTetapLainnyaResource\Pages;

use App\Filament\Resources\AsetTetapLainnyaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsetTetapLainnya extends EditRecord
{
    protected static string $resource = AsetTetapLainnyaResource::class;

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
        return 'Data aset tetap lainnya berhasil diperbarui';
    }
}
