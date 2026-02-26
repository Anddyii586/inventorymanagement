<x-filament-panels::page>

    <!-- {{-- CUSTOM WELCOME (KIRI PROFIL) --}}
    <div class="mb-6 flex items-center justify-between rounded-xl bg-gray-900 p-5">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-700 text-yellow-400 font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>

            <div>
                <div class="text-sm text-gray-400">
                    Selamat Datang
                </div>
                <div class="text-base font-semibold text-white">
                    {{ auth()->user()->name }}
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('filament.auth.logout') }}">
            @csrf
            <x-filament::button size="sm" color="gray">
                Keluar
            </x-filament::button>
        </form>
    </div> -->

    {{-- HEADER WIDGET --}}
    @if (count($this->getHeaderWidgets()))
        <x-filament-widgets::widgets
            :columns="$this->getHeaderWidgetsColumns()"
            :widgets="$this->getHeaderWidgets()"
        />
    @endif

    {{-- FOOTER WIDGET --}}
    @if (count($this->getFooterWidgets()))
        <x-filament-widgets::widgets
            :columns="$this->getFooterWidgetsColumns()"
            :widgets="$this->getFooterWidgets()"
        />
    @endif

</x-filament-panels::page>
