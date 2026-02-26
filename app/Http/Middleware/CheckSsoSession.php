<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\SsoSessionHelper;
use Symfony\Component\HttpFoundation\Response;
use PDOException;

class CheckSsoSession
{
    /**
     * Handle an incoming request.
     * 
     * Check if user is not authenticated, then try to read SSO session
     * and auto-login if session exists.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if SSO is disabled in config
        if (!SsoSessionHelper::isSsoEnabled()) {
            return $next($request);
        }

        // Skip if user is already authenticated
        if (Auth::check()) {
            return $next($request);
        }

        // Skip for login routes to avoid infinite loop
        if ($request->is('admin/login') || $request->is('login') || $request->is('sso/*')) {
            return $next($request);
        }

        // Cek koneksi SSO terlebih dahulu
        // Jika koneksi tidak tersedia, skip pengecekan session (tidak perlu log)
        if (!SsoSessionHelper::isSsoConnectionAvailable()) {
            return $next($request);
        }

        try {
            // Get SSO session ID from cookies
            $sessionId = SsoSessionHelper::getSsoSessionId($request);
            
            \Log::info('SSO Auto-login: Starting check', [
                'has_session_cookie' => !empty($sessionId),
                'session_id_preview' => $sessionId ? substr($sessionId, 0, 20) . '...' : null,
                'user_agent' => $request->userAgent(),
            ]);
            
            $user = null;
            
            if ($sessionId) {
                // If we have session ID from cookie, use it directly
                \Log::info('SSO Auto-login: Using cookie method', [
                    'session_id_preview' => substr($sessionId, 0, 20) . '...',
                ]);
                
                // Check SSO session and get user
                // Pass request object untuk validasi browser/client
                $user = SsoSessionHelper::checkAndGetUser($sessionId, $request);
                
                if ($user) {
                    \Log::info('SSO Auto-login: Cookie method - User found', [
                        'user_id' => $user->id,
                        'user_nama' => $user->nama ?? null,
                    ]);
                } else {
                    \Log::info('SSO Auto-login: Cookie method - User not found', [
                        'session_id_preview' => substr($sessionId, 0, 20) . '...',
                    ]);
                }
            } else {
                // If no session ID from cookie (cross-domain issue),
                // try to find matching session by browser/client information
                \Log::info('SSO Auto-login: No cookie found, trying browser match method', [
                    'user_agent' => $request->userAgent(),
                    'matching_by' => 'user_agent_only',
                ]);
                
                $user = SsoSessionHelper::findUserFromActiveSessions($request);
                
                if ($user) {
                    \Log::info('SSO Auto-login: Browser match method - User found', [
                        'user_id' => $user->id,
                        'user_nama' => $user->nama ?? null,
                    ]);
                } else {
                    \Log::info('SSO Auto-login: Browser match method - No matching session found', [
                        'user_agent' => $request->userAgent(),
                        'matching_by' => 'user_agent_only',
                    ]);
                }
            }
            
            if ($user) {
                Auth::login($user, true);
                $request->session()->regenerate();
                \Log::info('SSO Auto-login: SUCCESS - User logged in', [
                    'user_id' => $user->id,
                    'user_nama' => $user->nama ?? null,
                    'method' => $sessionId ? 'cookie' : 'browser_match',
                    'session_regenerated' => true,
                ]);
            } else {
                \Log::info('SSO Auto-login: FAILED - No user found', [
                    'has_session_cookie' => !empty($sessionId),
                    'method_attempted' => $sessionId ? 'cookie' : 'browser_match',
                ]);
            }

        } catch (\PDOException $e) {
            // Skip log untuk connection error/timeout
            // Ini normal jika SSO tidak bisa diakses
        } catch (\Exception $e) {
            // Hanya log error yang benar-benar penting
            // Skip log untuk connection error/timeout
            if (str_contains($e->getMessage(), 'timeout') || 
                str_contains($e->getMessage(), 'Connection')) {
                // Skip log untuk connection error/timeout
                return $next($request);
            }
            
            \Log::error('SSO Auto-login: Error', [
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}

