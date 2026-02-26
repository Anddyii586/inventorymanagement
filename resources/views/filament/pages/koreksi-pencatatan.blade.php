<x-filament-panels::page>
    <x-filament-panels::form wire:submit="create">
        {{ $this->form }}

        <div class="flex items-center justify-between gap-3 pt-6">
            <div></div>
            <div class="flex items-center gap-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="reset"
                >
                    Reset
                </x-filament::button>

                <x-filament::button
                    type="submit"
                    color="primary"
                >
                    Simpan Koreksi
                </x-filament::button>
            </div>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
