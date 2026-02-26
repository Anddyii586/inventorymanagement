<x-filament::modal width="md">
    <x-slot name="header">
        Preview QR Code
    </x-slot>

    <div class="text-center">
        <div class="mb-4 flex justify-center">
            {!! QrCode::size(300)->errorCorrection('H')->generate($url) !!}
        </div>

        <div class="text-sm text-gray-600">
            <p>URL: {{ $url }}</p>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-x-3">
            {{ $this->getAction('download') }}

            <x-filament::button color="gray" x-on:click="close">
                Close
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>