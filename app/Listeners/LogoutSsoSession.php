<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SsoConnectionService;
use PDOException;

class LogoutSsoSession
{
    /**
     * Handle the event.
     * 
     * Menjalankan logout SSO secara synchronous (langsung)
     * Dengan timeout yang sangat pendek dan cek koneksi terlebih dahulu
     * Jika SSO tidak bisa diakses dengan cepat, langsung skip tanpa error
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;
        
        if (!$user || !$user->pegawai_id) {
            return;
        }
        
        // Simpan data yang diperlukan untuk logout SSO
        $pegawaiId = $user->pegawai_id;
        
        // Jalankan logout SSO secara langsung (synchronous)
        // Sudah ada cek koneksi dan timeout pendek, jadi aman untuk dijalankan langsung
        $this->performSsoLogout($pegawaiId);
    }
    
    /**
     * Perform SSO logout
     * 
     * Method ini dijalankan secara synchronous saat user logout
     * Dengan cek koneksi terlebih dahulu dan timeout pendek untuk mencegah blocking
     */
    protected function performSsoLogout(int $pegawaiId): void
    {
        // Cek koneksi SSO terlebih dahulu
        if (!SsoConnectionService::isAvailable()) {
            Log::info('SSO Logout: SSO connection not available, skipping logout');
            return;
        }
        
        // Set timeout sedikit lebih panjang untuk operasi SSO (10 detik)
        // Jika SSO tidak bisa diakses, akan timeout tapi user tetap bisa logout
        $originalTimeout = ini_get('max_execution_time');
        $originalSocketTimeout = ini_get('default_socket_timeout');
        
        @set_time_limit(10);
        @ini_set('default_socket_timeout', 5); // Set socket timeout 5 detik
        
        try {
            $connection = 'sso';
            $ssoSessionTable = 'sessions';
            
            Log::info('SSO Logout: Starting logout for pegawai_id', [
                'pegawai_id' => $pegawaiId,
            ]);
            
            // Get all active sessions and find ones matching this user
            // Only get sessions from active users in SSO
            $recentSessions = [];
            try {
                $recentSessions = DB::connection($connection)->table($ssoSessionTable)
                    ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                    ->orderBy('last_activity', 'desc')
                    ->limit(100) // Check more sessions to find user's sessions
                    ->get(['id', 'payload']);
                    
                Log::info('SSO Logout: Found active sessions', [
                    'total_sessions' => count($recentSessions),
                ]);
            } catch (\PDOException $e) {
                // Connection timeout atau connection error
                Log::info('SSO Logout: Connection error atau timeout', [
                    'error' => $e->getMessage(),
                ]);
                return;
            } catch (\Exception $e) {
                // Jika query gagal (connection error, table tidak ada, dll), skip logout SSO
                Log::info('SSO Logout: Gagal mengambil session dari SSO', [
                    'error' => $e->getMessage(),
                ]);
                return;
            }
            
            if (empty($recentSessions)) {
                Log::info('SSO Logout: No active sessions found');
                return;
            }
            
            $deletedCount = 0;
            $checkedCount = 0;
            
            foreach ($recentSessions as $session) {
                try {
                    $checkedCount++;
                    
                    // Decode session payload
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
                            ->where('pegawai_id', $pegawaiId)
                            ->first(['id', 'uid', 'nama', 'pegawai_id']);
                        
                        if ($ssoUser) {
                            // Delete this session - it belongs to the logged out user
                            DB::connection($connection)->table($ssoSessionTable)
                                ->where('id', $session->id)
                                ->delete();
                            $deletedCount++;
                            
                            Log::info('SSO Logout: Deleted session', [
                                'session_id' => substr($session->id, 0, 20) . '...',
                                'uid' => $uid,
                                'pegawai_id' => $pegawaiId,
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
            
            Log::info('SSO Logout: Completed', [
                'pegawai_id' => $pegawaiId,
                'sessions_checked' => $checkedCount,
                'sessions_deleted' => $deletedCount,
            ]);
            
        } catch (\PDOException $e) {
            // Handle database connection errors (network issues, server down, etc.)
            Log::debug('SSO Logout: Database connection error', [
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            // Handle any other errors gracefully
            Log::debug('SSO Logout: Error saat logout SSO session', [
                'error' => $e->getMessage(),
            ]);
        } finally {
            // Restore original timeout settings
            if ($originalTimeout !== false) {
                set_time_limit($originalTimeout);
            }
            if ($originalSocketTimeout !== false) {
                ini_set('default_socket_timeout', $originalSocketTimeout);
            }
        }
    }
}

