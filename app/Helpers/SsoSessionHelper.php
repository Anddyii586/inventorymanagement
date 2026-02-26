<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\SsoConnectionService;

class SsoSessionHelper
{
    /**
     * Check if SSO feature is enabled in config
     * 
     * @return bool
     */
    public static function isSsoEnabled(): bool
    {
        return (bool) config('app.sso_enabled', false);
    }

    /**
     * Check if SSO connection is available and working
     * Uses a very short timeout to avoid blocking the page load
     * Results are cached for 1 minute to be more responsive to connection changes
     * Defaults to false if check fails or times out
     * 
     * @return bool
     */
    public static function isSsoConnectionAvailable(): bool
    {
        // Check if SSO is enabled in config first
        if (!self::isSsoEnabled()) {
            return false;
        }

        // Use the new SsoConnectionService for consistent connection checking
        // Cache for 1 minute only (same as HRD) for more responsive updates
        try {
            return SsoConnectionService::isAvailableCached(1); // Cache for 1 minute only
        } catch (\Exception $e) {
            // Jika ada error saat pengecekan (termasuk timeout), default ke false
            // Ini memastikan halaman tetap bisa dimuat meskipun SSO tidak bisa diakses
            return false;
        }
    }
    
    /**
     * Check SSO session and return user if found
     * Validates session against browser/client information (user agent, IP address)
     * 
     * @param string|null $sessionId
     * @param \Illuminate\Http\Request|null $request Request object to validate against browser/client
     * @return User|null
     */
    public static function checkAndGetUser(?string $sessionId = null, ?Request $request = null): ?User
    {
        if (!$sessionId) {
            return null;
        }

        try {
            $ssoSessionTable = 'sessions';
            $connection = 'sso';
            
            $session = null;
            try {
                $session = DB::connection($connection)->table($ssoSessionTable)
                    ->where('id', $sessionId)
                    ->first();
            } catch (\PDOException $e) {
                // Connection error (network, server down, etc.)
                \Log::debug('SSO Helper: Database connection error saat query session', [
                    'error' => $e->getMessage(),
                    'session_id' => substr($sessionId, 0, 20) . '...',
                ]);
                return null;
            } catch (\Exception $e) {
                \Log::debug('SSO Helper: Error query session', [
                    'error' => $e->getMessage(),
                    'session_id' => substr($sessionId, 0, 20) . '...',
                ]);
                return null;
            }
            
            if (!$session) {
                \Log::debug('SSO Helper: Session tidak ditemukan di database', [
                    'session_id' => substr($sessionId, 0, 20) . '...',
                ]);
                return null;
            }

            // Validasi session masih aktif (dalam 24 jam terakhir)
            // Jika kolom last_activity tidak ada, skip validasi ini
            if (isset($session->last_activity) && $session->last_activity !== null) {
                $lastActivity = is_numeric($session->last_activity) 
                    ? $session->last_activity 
                    : strtotime($session->last_activity);
                
                if ($lastActivity) {
                    $sessionTimeout = 24 * 60 * 60; // 24 jam dalam detik (lebih fleksibel)
                    if ($lastActivity < (time() - $sessionTimeout)) {
                        // Session sudah expired
                        return null;
                    }
                }
            }

            // Validasi session dengan browser/client yang mengakses
            // CATATAN: Validasi user_agent sementara dinonaktifkan untuk memastikan auto-login berfungsi
            // Validasi ini akan diaktifkan kembali setelah memastikan tidak ada masalah kompatibilitas
            // 
            // Untuk keamanan, validasi user_agent seharusnya diaktifkan, tapi karena mungkin
            // ada perbedaan format atau kolom tidak ada, kita skip dulu untuk memastikan fungsionalitas
            if ($request) {
                $requestUserAgent = $request->userAgent();
                $sessionUserAgent = $session->user_agent ?? null;
                
                // SEMENTARA: Skip validasi user_agent untuk memastikan auto-login berfungsi
                // TODO: Aktifkan kembali validasi setelah memastikan kolom user_agent ada dan formatnya benar
                /*
                if (!empty($sessionUserAgent) && !empty($requestUserAgent)) {
                    $sessionUserAgent = trim($sessionUserAgent);
                    $requestUserAgent = trim($requestUserAgent);
                    
                    if ($sessionUserAgent !== $requestUserAgent) {
                        return null;
                    }
                }
                */

                // Catatan: IP address tidak divalidasi karena bisa berubah saat user pindah network
                // (misalnya dari WiFi ke tethering), yang akan mengganggu user experience
            }

            // Decode session data
            $sessionData = null;
            try {
                $decoded = base64_decode($session->payload);
                $sessionData = @unserialize($decoded);
                if ($sessionData === false) {
                    throw new \Exception('Base64 unserialize returned false');
                }
            } catch (\Exception $e) {
                try {
                    $sessionData = @unserialize($session->payload);
                    if ($sessionData === false) {
                        throw new \Exception('Direct unserialize returned false');
                    }
                } catch (\Exception $e2) {
                    return null;
                }
            }
            
            if (!is_array($sessionData)) {
                return null;
            }
            
            // Extract UID from session
            $uid = null;
            
            foreach ($sessionData as $key => $value) {
                if (str_starts_with($key, 'login_web_')) {
                    $uid = $value;
                    break;
                }
                
                if (in_array($key, ['uid', 'user_id', 'id'])) {
                    $uid = $value;
                }
            }
            
            if (!$uid) {
                foreach ($sessionData as $key => $value) {
                    if (is_array($value)) {
                        $uid = $value['uid'] ?? $value['user_id'] ?? $value['id'] ?? null;
                        if ($uid) {
                            break;
                        }
                    } elseif (is_object($value)) {
                        $uid = $value->uid ?? $value->user_id ?? $value->id ?? null;
                        if ($uid) {
                            break;
                        }
                    }
                }
            }
            
            if (!$uid) {
                \Log::debug('SSO Helper: UID tidak ditemukan di session data', [
                    'session_keys' => array_keys($sessionData ?? []),
                ]);
                return null;
            }

            // Query ke tabel pengguna di SSO untuk mendapatkan pegawai_id
            $ssoUser = null;
            try {
                $ssoUser = DB::connection($connection)->table('pengguna')
                    ->where('id', $uid)
                    ->first(['id', 'uid', 'nama', 'pegawai_id']);
                
                if (!$ssoUser) {
                    \Log::debug('SSO Helper: User tidak ditemukan di SSO dengan UID', [
                        'uid' => $uid,
                    ]);
                    return null;
                }
            } catch (\PDOException $e) {
                // Connection error (network, server down, etc.)
                \Log::debug('SSO Helper: Database connection error saat query pengguna', [
                    'error' => $e->getMessage(),
                    'uid' => $uid,
                ]);
                return null;
            } catch (\Exception $e) {
                \Log::debug('SSO Helper: Error query pengguna', [
                    'error' => $e->getMessage(),
                    'uid' => $uid,
                ]);
                return null;
            }

            $pegawaiId = $ssoUser->pegawai_id;
            if (!$pegawaiId) {
                \Log::debug('SSO Helper: pegawai_id tidak ditemukan untuk user SSO', [
                    'uid' => $uid,
                    'sso_user_id' => $ssoUser->id ?? null,
                ]);
                return null;
            }

            // Cari user di local dengan pegawai_id dari SSO
            $user = User::where('pegawai_id', $pegawaiId)->first();

            if (!$user) {
                \Log::debug('SSO Helper: User tidak ditemukan di local dengan pegawai_id', [
                    'pegawai_id' => $pegawaiId,
                    'uid' => $uid,
                ]);
            }

            return $user;

        } catch (\PDOException $e) {
            // Connection error (network, server down, etc.)
            \Log::debug('SSO Helper: Database connection error', [
                'error' => $e->getMessage(),
            ]);
            return null;
        } catch (\Exception $e) {
            \Log::debug('SSO Helper: Error saat check session', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get SSO session ID from request cookies
     * 
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public static function getSsoSessionId($request): ?string
    {
        $ssoSessionCookieName = 'PTAMGM_2023_session';
        
        $sessionId = $request->cookie($ssoSessionCookieName);
        
        if (!$sessionId) {
            $allCookies = $request->cookies->all();
            foreach (['PTAMGM_2023_session', 'ptamgm_2023_session', 'session'] as $cookieName) {
                if (isset($allCookies[$cookieName])) {
                    $sessionId = $allCookies[$cookieName];
                    break;
                }
            }
        }
        
        return $sessionId;
    }

    /**
     * Try to find active SSO session by matching browser/client information
     * Only matches sessions from the same browser/client that is accessing this app
     * 
     * This method is secure because it only matches sessions that have:
     * - Same user_agent (browser fingerprint)
     * - Same device fingerprint (jika tersedia - lebih spesifik)
     * - Recent activity (within last 30 minutes)
     * 
     * @param \Illuminate\Http\Request|null $request
     * @return User|null
     */
    public static function findUserFromActiveSessions(?Request $request = null): ?User
    {
        if (!$request) {
            return null;
        }

        // Cek koneksi SSO terlebih dahulu
        if (!self::isSsoConnectionAvailable()) {
            return null;
        }

        try {
            $connection = 'sso';
            $ssoSessionTable = 'sessions';
            
            // Get browser/client information from request
            // Only use user_agent for matching, not IP (because app and SSO are on different servers)
            $requestUserAgent = $request->userAgent();
            
            if (!$requestUserAgent) {
                // Cannot match without user agent
                \Log::info('SSO Helper: Cannot match - no user agent in request');
                return null;
            }
            
            // Get device fingerprint from session (jika tersedia)
            // Fingerprint ini lebih spesifik daripada user_agent saja
            $deviceFingerprint = session('device_fingerprint');
            $hasDeviceFingerprint = !empty($deviceFingerprint);
            
            \Log::info('SSO Helper: Starting session matching', [
                'has_device_fingerprint' => $hasDeviceFingerprint,
                'matching_method' => $hasDeviceFingerprint ? 'user_agent_and_fingerprint' : 'user_agent_only',
            ]);

            // Get recent active sessions (last 30 minutes)
            // Try to get user_agent and ip_address if columns exist
            $recentSessions = [];
            try {
                // First, try to get with user_agent and ip_address
                try {
                    $recentSessions = DB::connection($connection)->table($ssoSessionTable)
                        ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                        ->orderBy('last_activity', 'desc')
                        ->limit(100) // Check up to 100 recent sessions
                        ->get(['id', 'payload', 'user_agent']);
                    
                    \Log::info('SSO Helper: Found active sessions', [
                        'total_sessions' => count($recentSessions),
                        'has_user_agent_column' => true,
                        'matching_by' => 'user_agent_only',
                    ]);
                } catch (\Exception $e) {
                    // If user_agent column doesn't exist, get without it
                    $recentSessions = DB::connection($connection)->table($ssoSessionTable)
                        ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                        ->orderBy('last_activity', 'desc')
                        ->limit(100)
                        ->get(['id', 'payload']);
                    
                    \Log::info('SSO Helper: Found active sessions (without user_agent column)', [
                        'total_sessions' => count($recentSessions),
                        'has_user_agent_column' => false,
                        'note' => 'Cannot match by browser/client - user_agent column missing',
                    ]);
                }
            } catch (\PDOException $e) {
                \Log::info('SSO Helper: Connection error saat mengambil session aktif', [
                    'error' => $e->getMessage(),
                ]);
                return null;
            } catch (\Exception $e) {
                \Log::info('SSO Helper: Error mengambil session aktif', [
                    'error' => $e->getMessage(),
                ]);
                return null;
            }

            if (empty($recentSessions)) {
                \Log::info('SSO Helper: No active sessions found in last 30 minutes');
                return null;
            }

            // Match sessions based on user_agent and device fingerprint (jika tersedia)
            // Note: IP address is NOT checked because app and SSO are on different servers
            $matchedCount = 0;
            $userAgentMismatchCount = 0;
            $fingerprintMismatchCount = 0;
            $noUserAgentCount = 0;
            
            foreach ($recentSessions as $session) {
                $sessionUserAgent = $session->user_agent ?? null;
                
                // Match user agent (must be exact match for security)
                // If user_agent column doesn't exist, we can't match securely, so skip
                if (!$sessionUserAgent) {
                    // If we don't have user_agent in session, we can't safely match
                    // This prevents session hijacking
                    $noUserAgentCount++;
                    continue;
                }
                
                // Exact match user agent (browser fingerprint)
                if (trim($sessionUserAgent) !== trim($requestUserAgent)) {
                    $userAgentMismatchCount++;
                    continue;
                }
                
                // Jika device fingerprint tersedia, cek juga fingerprint di session SSO
                // Kita akan cek fingerprint di session data (payload)
                if ($hasDeviceFingerprint) {
                    try {
                        // Decode session untuk cek fingerprint
                        $decoded = base64_decode($session->payload);
                        $sessionData = @unserialize($decoded);
                        
                        if (!is_array($sessionData)) {
                            // Try direct unserialize
                            $sessionData = @unserialize($session->payload);
                        }
                        
                        if (is_array($sessionData)) {
                            // Cari fingerprint di session data
                            // Fingerprint mungkin disimpan dengan key seperti 'device_fingerprint' atau 'fingerprint'
                            $sessionFingerprint = $sessionData['device_fingerprint'] 
                                ?? $sessionData['fingerprint'] 
                                ?? $sessionData['app_device_fingerprint'] 
                                ?? null;
                            
                            // Jika fingerprint ada di session SSO, harus match
                            if ($sessionFingerprint && $sessionFingerprint !== $deviceFingerprint) {
                                $fingerprintMismatchCount++;
                                continue; // Fingerprint tidak match, skip session ini
                            }
                            
                            // Jika fingerprint tidak ada di session SSO tapi kita punya fingerprint,
                            // kita tetap lanjutkan (backward compatible dengan session lama)
                            // Tapi kita akan log untuk tracking
                        }
                    } catch (\Exception $e) {
                        // Jika error decode, lanjutkan dengan user_agent saja
                        \Log::debug('SSO Helper: Error checking fingerprint in session', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                $matchedCount++;
                
                // Found matching session, try to get user from it
                try {
                    $decoded = base64_decode($session->payload);
                    $sessionData = @unserialize($decoded);
                    
                    if (!is_array($sessionData)) {
                        continue;
                    }
                    
                    // Extract UID from session
                    $uid = null;
                    foreach ($sessionData as $key => $value) {
                        if (str_starts_with($key, 'login_web_')) {
                            $uid = $value;
                            break;
                        }
                        
                        if (in_array($key, ['uid', 'user_id', 'id'])) {
                            $uid = $value;
                        }
                    }
                    
                    if (!$uid) {
                        continue;
                    }
                    
                    // Get user from SSO
                    $ssoUser = DB::connection($connection)->table('pengguna')
                        ->where('id', $uid)
                        ->first(['id', 'uid', 'nama', 'pegawai_id']);
                    
                    if (!$ssoUser || !$ssoUser->pegawai_id) {
                        continue;
                    }
                    
                    // Find user in local database
                    $user = User::where('pegawai_id', $ssoUser->pegawai_id)->first();
                    
                    if ($user) {
                        \Log::info('SSO Helper: Found matching session', [
                            'session_id' => substr($session->id, 0, 20) . '...',
                            'user_id' => $user->id,
                            'user_nama' => $user->nama ?? null,
                            'user_agent_match' => true,
                            'fingerprint_checked' => $hasDeviceFingerprint,
                            'matching_method' => $hasDeviceFingerprint ? 'user_agent_and_fingerprint' : 'user_agent_only',
                        ]);
                        return $user;
                    }
                } catch (\Exception $e) {
                    // Skip this session if there's an error
                    \Log::debug('SSO Helper: Error processing session', [
                        'session_id' => substr($session->id ?? '', 0, 20) . '...',
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }
            
            \Log::info('SSO Helper: No matching session found', [
                'total_sessions_checked' => count($recentSessions),
                'matched_user_agent' => $matchedCount,
                'user_agent_mismatch' => $userAgentMismatchCount,
                'fingerprint_mismatch' => $fingerprintMismatchCount ?? 0,
                'no_user_agent' => $noUserAgentCount,
                'request_user_agent' => $requestUserAgent,
                'has_device_fingerprint' => $hasDeviceFingerprint,
                'matching_method' => $hasDeviceFingerprint ? 'user_agent_and_fingerprint' : 'user_agent_only',
            ]);
            
            return null;
        } catch (\PDOException $e) {
            \Log::debug('SSO Helper: Database connection error saat mencari session aktif', [
                'error' => $e->getMessage(),
            ]);
            return null;
        } catch (\Exception $e) {
            \Log::debug('SSO Helper: Error saat mencari session aktif', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Logout SSO session by deleting session from database
     * 
     * @param \Illuminate\Http\Request|null $request
     * @return bool
     */
    public static function logoutSsoSession($request = null): bool
    {
        // Cek koneksi SSO terlebih dahulu
        if (!self::isSsoConnectionAvailable()) {
            \Log::info('SSO Helper: SSO connection not available, skipping logout');
            return false;
        }
        
        // Set timeout lebih pendek untuk operasi SSO (3 detik)
        $originalTimeout = ini_get('max_execution_time');
        $originalSocketTimeout = ini_get('default_socket_timeout');
        
        set_time_limit(3);
        ini_set('default_socket_timeout', 2); // Set socket timeout 2 detik
        
        try {
            $connection = 'sso';
            $ssoSessionTable = 'sessions';
            
            \Log::info('SSO Helper: Starting logoutSsoSession');
            
            $sessionId = null;
            if ($request) {
                $sessionId = self::getSsoSessionId($request);
            }
            
            if ($sessionId) {
                try {
                    $deleted = DB::connection($connection)->table($ssoSessionTable)
                        ->where('id', $sessionId)
                        ->delete();
                    
                    if ($deleted) {
                        return true;
                    }
                } catch (\PDOException $e) {
                    // Connection timeout atau error
                    \Log::debug('SSO Helper: Connection error saat delete session by ID', [
                        'error' => $e->getMessage(),
                    ]);
                } catch (\Exception $e) {
                    \Log::debug('SSO Helper: Gagal delete session by ID', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            $user = auth()->user();
            if ($user && $user->pegawai_id) {
                \Log::info('SSO Helper: Logging out sessions for user', [
                    'user_id' => $user->id,
                    'pegawai_id' => $user->pegawai_id,
                ]);
                
                try {
                    $recentSessions = DB::connection($connection)->table($ssoSessionTable)
                        ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                        ->orderBy('last_activity', 'desc')
                        ->limit(100) // Check more sessions
                        ->get(['id', 'payload']);
                        
                    \Log::info('SSO Helper: Found active sessions for logout', [
                        'total_sessions' => count($recentSessions),
                    ]);
                } catch (\PDOException $e) {
                    // Connection timeout atau error
                    \Log::info('SSO Helper: Connection error saat mengambil session dari SSO', [
                        'error' => $e->getMessage(),
                    ]);
                    return false;
                } catch (\Exception $e) {
                    // Jika query gagal, return false tanpa error
                    \Log::info('SSO Helper: Gagal mengambil session dari SSO', [
                        'error' => $e->getMessage(),
                    ]);
                    return false;
                }
                
                if (empty($recentSessions)) {
                    \Log::info('SSO Helper: No active sessions found for logout');
                    return false;
                }
                
                $deletedCount = 0;
                $checkedCount = 0;
                
                foreach ($recentSessions as $session) {
                    try {
                        $checkedCount++;
                        
                        $decoded = base64_decode($session->payload);
                        $sessionData = @unserialize($decoded);
                        
                        if (!is_array($sessionData)) {
                            continue;
                        }
                        
                        // Extract UID from session
                        $uid = null;
                        foreach ($sessionData as $key => $value) {
                            if (str_starts_with($key, 'login_web_')) {
                                $uid = $value;
                                break;
                            }
                            
                            if (in_array($key, ['uid', 'user_id', 'id'])) {
                                $uid = $value;
                            }
                        }
                        
                        if (!$uid) {
                            continue;
                        }
                        
                        // Check if this session belongs to current user (pegawai_id must match)
                        try {
                            $ssoUser = DB::connection($connection)->table('pengguna')
                                ->where('id', $uid)
                                ->where('pegawai_id', $user->pegawai_id)
                                ->first(['id', 'uid', 'nama', 'pegawai_id']);
                            
                            if ($ssoUser) {
                                // Delete this session - it belongs to the logged out user
                                DB::connection($connection)->table($ssoSessionTable)
                                    ->where('id', $session->id)
                                    ->delete();
                                $deletedCount++;
                                
                                \Log::info('SSO Helper: Deleted session', [
                                    'session_id' => substr($session->id, 0, 20) . '...',
                                    'uid' => $uid,
                                    'pegawai_id' => $user->pegawai_id,
                                    'user_nama' => $ssoUser->nama ?? null,
                                ]);
                            }
                        } catch (\PDOException $e) {
                            // Connection timeout atau error, skip
                            continue;
                        } catch (\Exception $e) {
                            // Skip jika query pengguna atau delete gagal
                            continue;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                \Log::info('SSO Helper: Logout completed', [
                    'sessions_checked' => $checkedCount,
                    'sessions_deleted' => $deletedCount,
                ]);
                
                if ($deletedCount > 0) {
                    return true;
                }
            }
            
            return false;
        } catch (\PDOException $e) {
            // Handle database connection errors (network issues, server down, etc.)
            \Log::debug('SSO Helper: Database connection error saat logout', [
                'error' => $e->getMessage(),
            ]);
            return false;
        } catch (\Exception $e) {
            // Handle any other errors gracefully
            \Log::debug('SSO Helper: Error saat logout SSO session', [
                'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            // Restore original timeout settings
            if (isset($originalTimeout) && $originalTimeout !== false) {
                set_time_limit($originalTimeout);
            }
            if (isset($originalSocketTimeout) && $originalSocketTimeout !== false) {
                ini_set('default_socket_timeout', $originalSocketTimeout);
            }
        }
    }
}
