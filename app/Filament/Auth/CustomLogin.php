<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use App\Helpers\SsoSessionHelper;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use PDOException;

class CustomLogin extends Login
{
    /**
     * The view to use for the login page
     */
    protected static string $view = 'filament.pages.custom-login';

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getUserFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getUserFormComponent(): Component
    {
        return TextInput::make('user')
            ->label('Nama pengguna')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'user' => $data['user'],
            'password' => $data['password'],
        ];
    }

    public function mount(): void
    {
        parent::mount();
        
        // Check SSO session and auto-login if user is found
        $this->checkSsoSession();
    }
    
    /**
     * Check if SSO feature is enabled in config
     * 
     * @return bool
     */
    public function isSsoEnabled(): bool
    {
        return SsoSessionHelper::isSsoEnabled();
    }

    /**
     * Check if SSO connection is available
     * Wrapped in try-catch to ensure page loads even if check fails
     * 
     * @return bool
     */
    public function isSsoAvailable(): bool
    {
        // First check if SSO is enabled in config
        if (!$this->isSsoEnabled()) {
            return false;
        }

        try {
            return SsoSessionHelper::isSsoConnectionAvailable();
        } catch (\Exception $e) {
            // Jika pengecekan gagal (timeout, dll), default ke false
            // Ini memastikan halaman login tetap bisa dimuat
            \Log::debug('SSO CustomLogin: Error checking SSO availability', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check SSO session and auto-login if user is found
     */
    protected function checkSsoSession(): void
    {
        // Skip if SSO is disabled in config
        if (!$this->isSsoEnabled()) {
            return;
        }

        // Skip if already authenticated
        if (Auth::check()) {
            return;
        }

        // Cek koneksi SSO terlebih dahulu
        // Jika koneksi tidak tersedia, skip pengecekan session (tidak perlu log)
        if (!SsoSessionHelper::isSsoConnectionAvailable()) {
            return;
        }

        try {
            $request = request();
            
            // Get SSO session ID from cookies
            $sessionId = SsoSessionHelper::getSsoSessionId($request);
            
            \Log::info('SSO CustomLogin: Starting check', [
                'has_session_cookie' => !empty($sessionId),
                'session_id_preview' => $sessionId ? substr($sessionId, 0, 20) . '...' : null,
                'user_agent' => $request->userAgent(),
            ]);
            
            $user = null;
            
            if ($sessionId) {
                // If we have session ID from cookie, use it directly
                \Log::info('SSO CustomLogin: Using cookie method', [
                    'session_id_preview' => substr($sessionId, 0, 20) . '...',
                ]);
                
                // Check SSO session and get user
                // Pass request object untuk validasi browser/client
                $user = SsoSessionHelper::checkAndGetUser($sessionId, $request);
                
                if ($user) {
                    \Log::info('SSO CustomLogin: Cookie method - User found', [
                        'user_id' => $user->id,
                        'user_nama' => $user->nama ?? null,
                    ]);
                } else {
                    \Log::info('SSO CustomLogin: Cookie method - User not found', [
                        'session_id_preview' => substr($sessionId, 0, 20) . '...',
                    ]);
                }
            } else {
                // If no session ID from cookie (cross-domain issue),
                // try to find matching session by browser/client information
                \Log::info('SSO CustomLogin: No cookie found, trying browser match method', [
                    'user_agent' => $request->userAgent(),
                    'matching_by' => 'user_agent_only',
                ]);
                
                $user = SsoSessionHelper::findUserFromActiveSessions($request);
                
                if ($user) {
                    \Log::info('SSO CustomLogin: Browser match method - User found', [
                        'user_id' => $user->id,
                        'user_nama' => $user->nama ?? null,
                    ]);
                } else {
                    \Log::info('SSO CustomLogin: Browser match method - No matching session found', [
                        'user_agent' => $request->userAgent(),
                        'matching_by' => 'user_agent_only',
                    ]);
                }
            }
            
            if ($user) {
                Auth::login($user, true);
                $request->session()->regenerate();
                \Log::info('SSO CustomLogin: SUCCESS - User logged in and redirecting', [
                    'user_id' => $user->id,
                    'user_nama' => $user->nama ?? null,
                    'method' => $sessionId ? 'cookie' : 'browser_match',
                    'session_regenerated' => true,
                ]);
                $this->redirect(Filament::getUrl(), navigate: true);
            } else {
                \Log::info('SSO CustomLogin: FAILED - No user found', [
                    'has_session_cookie' => !empty($sessionId),
                    'method_attempted' => $sessionId ? 'cookie' : 'browser_match',
                ]);
            }
        } catch (\Exception $e) {
            // Hanya log error yang benar-benar penting
            // Skip log untuk error biasa seperti connection timeout
            if (str_contains($e->getMessage(), 'timeout') || 
                str_contains($e->getMessage(), 'Connection') ||
                $e instanceof \PDOException) {
                // Skip log untuk connection error/timeout
                return;
            }
            
            \Log::error('SSO CustomLogin: Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
