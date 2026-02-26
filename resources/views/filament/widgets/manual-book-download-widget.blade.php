<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-900/10">
                    <x-filament::icon
                        name="heroicon-o-book-open"
                        class="h-6 w-6 text-primary-600 dark:text-primary-400"
                    />
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white sm:whitespace-nowrap overflow-hidden text-ellipsis">
                        Manual Book Sistem Pendataan Aset PTAMGM
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate sm:whitespace-normal">
                        Download panduan lengkap penggunaan Sistem Pendataan Aset PTAMGM
                    </p>
                </div>
            </div>
            <div class="shrink-0">
                {{ $this->getDownloadAction() }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
