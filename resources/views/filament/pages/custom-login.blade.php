<x-filament-panels::page.simple>
    <x-slot name="heading">
        <h2 class="text-xl font-bold tracking-tight text-center text-gray-900 dark:text-white">
            SISTEM PENDATAAN ASET PTAMGM
        </h2>
    </x-slot>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
    
    {{-- SSO Login Button - Only show if SSO connection is available --}}
    @if($this->isSsoAvailable())
    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">atau</span>
            </div>
        </div>
        <div class="mt-6">
            <a href="{{ route('sso.redirect', ['redirect' => request()->url()]) }}" 
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-600">
                Login dengan SSO
            </a>
        </div>
    </div>
    @endif
    
    {{-- Device Fingerprint Script untuk SSO --}}
    @push('scripts')
    @vite('resources/js/device-fingerprint.js')
    @endpush
</x-filament-panels::page.simple>

