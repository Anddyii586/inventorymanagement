<?php

namespace App\Filament\Resources\TanahResource\Components;

use Filament\Actions\StaticAction;
use Filament\Support\Components\ViewComponent;
use Illuminate\Contracts\View\View;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodePreviewModal extends ViewComponent implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public string $id;
    public string $url;

    public function mount(string $id): void
    {
        $this->id = $id;
        $this->url = route('public.tanah.detail', $id);
    }

    public function download(): void
    {
        $qrcode = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($this->url);

        $filename = "qrcode-tanah-{$this->id}.svg";
        \Storage::disk('public')->put($filename, $qrcode);

        response()->download(storage_path("app/public/{$filename}"))->deleteFileAfterSend()->send();
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function getActions(): array
    {
        return [
            StaticAction::make('download')
                ->label('Download QR Code')
                ->icon('heroicon-m-arrow-down-tray')
                ->action('download'),
        ];
    }

    public function render(): View
    {
        return view('filament.resources.tanah-resource.components.qr-code-preview-modal');
    }
}