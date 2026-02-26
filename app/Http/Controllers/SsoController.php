<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pegawai;
use App\Helpers\SsoSessionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SsoController extends Controller
{
    /**
     * Redirect to SSO login page
     * 
     * PENTING: SSO harus mengirim data user melalui URL parameter saat redirect ke callback URL
     * karena browser tidak bisa membaca cookies dari domain lain (Same-Origin Policy)
     */
    public function redirect(Request $request)
    {
        // Check if SSO is enabled in config
        if (!SsoSessionHelper::isSsoEnabled()) {
            return redirect('/admin/login')
                ->with('error', 'Fitur SSO tidak aktif.');
        }

        // Get the callback URL - this is where SSO will redirect back to
        $callbackUrl = url('/sso/callback');
        $baseUrl = url('/');
        
        // Get the intended URL (where user wants to go after login)
        $intendedUrl = $request->get('redirect', url('/admin'));
        
        // Store the intended URL in session for redirect after login
        session(['sso_intended_url' => $intendedUrl]);
        
        // Build SSO URL with callback parameter
        // The SSO system should redirect back to our callback URL after successful login
        // SSO HARUS mengirim data user melalui URL parameter seperti:
        // callback?nik=201103266&uid=201103266&nama=User Name
        $ssoUrl = 'http://app.ptamgm.net/';
        
        // Add callback URL as parameter - SSO should redirect here after login
        $ssoUrl .= '?callback=' . urlencode($callbackUrl);
        $ssoUrl .= '&base_url=' . urlencode($baseUrl);
        $ssoUrl .= '&return_url=' . urlencode($callbackUrl);
        
        return redirect($ssoUrl);
    }

    /**
     * Handle SSO callback
     * 
     * PENTING: SSO harus mengirim data user melalui URL parameter karena
     * browser tidak bisa membaca cookies dari domain lain.
     * 
     * Contoh URL yang diharapkan:
     * /sso/callback?nik=201103266&uid=201103266&nama=User Name
     */
    public function callback(Request $request)
    {
        // Check if SSO is enabled in config
        if (!SsoSessionHelper::isSsoEnabled()) {
            return redirect('/admin/login')
                ->with('error', 'Fitur SSO tidak aktif.');
        }

        try {
            // SSO HARUS mengirim data user melalui URL parameter
            // karena browser tidak bisa membaca cookies dari domain lain
            $uid = $request->get('uid');
            $nik = $request->get('nik');
            
            // Also check for common SSO parameter names
            if (!$uid) {
                $uid = $request->get('user_id') ?? $request->get('userid') ?? $request->get('id');
            }
            if (!$nik) {
                $nik = $request->get('employee_id') ?? $request->get('employeeid') ?? $request->get('nip');
            }
            
            // If not in query, try to get from SSO session via API call
            // Check cookies that might contain session info
            if (!$uid && !$nik) {
                // Try to get session from cookies
                $allCookies = $request->cookies->all();
                $sessionCookie = null;
                
                // Look for common session cookie names
                foreach (['session', 'sso_session', 'laravel_session', 'PHPSESSID', 'session_id'] as $cookieName) {
                    if (isset($allCookies[$cookieName])) {
                        $sessionCookie = $allCookies[$cookieName];
                        break;
                    }
                }
                
                if ($sessionCookie) {
                    // Try to fetch user info from SSO API using the session cookie
                    try {
                        $response = Http::withHeaders([
                            'Cookie' => 'session=' . $sessionCookie,
                        ])->timeout(5)->get('http://app.ptamgm.net/api/user');
                        
                        if ($response->successful()) {
                            $userData = $response->json();
                            $uid = $userData['uid'] ?? $userData['user_id'] ?? $userData['id'] ?? null;
                            $nik = $userData['nik'] ?? $userData['nip'] ?? $userData['employee_id'] ?? $uid;
                        }
                    } catch (\Exception $e) {
                        // Silent fail
                    }
                }
                
                // Also try to get from POST data if it's a POST request
                if (!$uid && !$nik && $request->isMethod('post')) {
                    $uid = $request->input('uid');
                    $nik = $request->input('nik');
                }
            }
            
            // Use NIK or UID to find user
            $identifier = $nik ?? $uid;
            
            if (!$identifier) {
                // Return to login with error message
                return redirect('/admin/login')
                    ->with('error', 'Tidak dapat membaca data pengguna dari SSO. SSO harus mengirim NIK/UID melalui URL parameter saat redirect. Silakan hubungi administrator untuk mengkonfigurasi SSO dengan benar.');
            }
            
            // Find user by NIK (from pegawai relationship) or by user field
            $user = null;
            
            // First, try to find by pegawai NIK
            $pegawai = Pegawai::where('nik', $identifier)->first();
            if ($pegawai) {
                $user = User::where('pegawai_id', $pegawai->id)->first();
            }
            
            // If not found, try to find by user field (which might be NIK)
            if (!$user) {
                $user = User::where('user', $identifier)->first();
            }
            
            // If still not found, try to find by any field that might contain NIK
            if (!$user) {
                // Try to find user where user field matches identifier
                $user = User::where('user', 'like', "%{$identifier}%")->first();
            }
            
            if (!$user) {
                return redirect('/admin/login')
                    ->with('error', 'Pengguna dengan NIK/UID ' . $identifier . ' tidak ditemukan dalam sistem.');
            }
            
            // Login the user
            Auth::login($user, true); // true = remember me
            
            // Get intended URL or default to admin dashboard
            $intendedUrl = session('sso_intended_url', url('/admin'));
            session()->forget('sso_intended_url');
            
            return redirect($intendedUrl)
                ->with('success', 'Login berhasil melalui SSO.');
                
        } catch (\Exception $e) {
            return redirect('/admin/login')
                ->with('error', 'Terjadi kesalahan saat proses login SSO: ' . $e->getMessage());
        }
    }

    /**
     * Check SSO session and return user data
     * This method tries to read session from SSO by checking cookies
     */
    public function checkSession(Request $request)
    {
        try {
            $sessionData = [];
            $userData = null;
            
            // Get all cookies from request
            $allCookies = $request->cookies->all();
            
            // Try to get session cookie - prioritize SSO cookie
            $sessionCookie = null;
            $cookieName = null;
            
            // First, try SSO cookie name
            $ssoCookieName = 'PTAMGM_2023_session';
            if (isset($allCookies[$ssoCookieName])) {
                $sessionCookie = $allCookies[$ssoCookieName];
                $cookieName = $ssoCookieName;
            } else {
                // Try other common session cookie names
                foreach (['PTAMGM_2023_session', 'ptamgm_2023_session', 'session', 'sso_session', 'laravel_session', 'PHPSESSID', 'session_id', 'app_session'] as $name) {
                    if (isset($allCookies[$name])) {
                        $sessionCookie = $allCookies[$name];
                        $cookieName = $name;
                        break;
                    }
                }
            }
            
            $sessionData['cookies_found'] = array_keys($allCookies);
            $sessionData['session_cookie_name'] = $cookieName;
            $sessionData['has_session_cookie'] = !is_null($sessionCookie);
            $sessionData['session_cookie_value'] = $sessionCookie ? substr($sessionCookie, 0, 20) . '...' : null;
            
            // Also try to read from database directly
            if ($sessionCookie && $cookieName === 'PTAMGM_2023_session') {
                try {
                    // First, try to read session from database to see what's in it
                    // Menggunakan connection sso, table: sessions
                    $connection = 'sso';
                    $ssoSessionTable = 'sessions';
                    $session = null;
                    
                    try {
                        $session = DB::connection($connection)->table($ssoSessionTable)
                            ->where('id', $sessionCookie)
                            ->first();
                        
                        if ($session) {
                            $sessionData['session_found_in_db'] = true;
                            $sessionData['session_connection'] = $connection;
                            $sessionData['session_table'] = $ssoSessionTable;
                            $sessionData['session_last_activity'] = $session->last_activity ?? null;
                            
                            // Try to decode session and extract UID
                            try {
                                $decodedSession = unserialize($session->payload, ['allowed_classes' => false]);
                                $sessionData['session_keys'] = array_keys($decodedSession);
                                $sessionData['session_data_sample'] = array_slice($decodedSession, 0, 10, true);
                                
                                // Extract UID from session
                                $uid = null;
                                foreach ($decodedSession as $key => $value) {
                                    if (str_starts_with($key, 'login_web_')) {
                                        $uid = $value;
                                        break;
                                    }
                                    if (in_array($key, ['uid', 'user_id', 'id'])) {
                                        $uid = $value;
                                    }
                                }
                                if (!$uid) {
                                    foreach ($decodedSession as $key => $value) {
                                        if (is_array($value)) {
                                            $uid = $value['uid'] ?? $value['user_id'] ?? $value['id'] ?? null;
                                            if ($uid) break;
                                        } elseif (is_object($value)) {
                                            $uid = $value->uid ?? $value->user_id ?? $value->id ?? null;
                                            if ($uid) break;
                                        }
                                    }
                                }
                                $sessionData['uid_found'] = $uid;
                            } catch (\Exception $e) {
                                try {
                                    $decoded = base64_decode($session->payload);
                                    if ($decoded === false) {
                                        throw new \Exception('Base64 decode failed');
                                    }
                                    $decodedSession = unserialize($decoded, ['allowed_classes' => false]);
                                    $sessionData['session_keys'] = array_keys($decodedSession);
                                    $sessionData['session_data_sample'] = array_slice($decodedSession, 0, 10, true);
                                    
                                    // Extract UID from session
                                    $uid = null;
                                    foreach ($decodedSession as $key => $value) {
                                        if (str_starts_with($key, 'login_web_')) {
                                            $uid = $value;
                                            break;
                                        }
                                        if (in_array($key, ['uid', 'user_id', 'id'])) {
                                            $uid = $value;
                                        }
                                    }
                                    if (!$uid) {
                                        foreach ($decodedSession as $key => $value) {
                                            if (is_array($value)) {
                                                $uid = $value['uid'] ?? $value['user_id'] ?? $value['id'] ?? null;
                                                if ($uid) break;
                                            } elseif (is_object($value)) {
                                                $uid = $value->uid ?? $value->user_id ?? $value->id ?? null;
                                                if ($uid) break;
                                            }
                                        }
                                    }
                                    $sessionData['uid_found'] = $uid;
                                } catch (\Exception $e2) {
                                    $sessionData['session_decode_error'] = $e2->getMessage();
                                    \Log::debug('SSO Controller: Error decoding session payload', ['error' => $e2->getMessage()]);
                                }
                            }
                        } else {
                            $sessionData['session_found_in_db'] = false;
                            // Try to get sample sessions for debugging
                            try {
                                $sampleSessions = DB::connection($connection)->table($ssoSessionTable)
                                    ->select('id', 'last_activity')
                                    ->orderBy('last_activity', 'desc')
                                    ->limit(3)
                                    ->get();
                                
                                if ($sampleSessions->count() > 0) {
                                    $sessionData['table_exists'] = $ssoSessionTable;
                                    $sessionData['sample_session_ids'] = $sampleSessions->pluck('id')->map(fn($id) => substr($id, 0, 30))->toArray();
                                    $sessionData['session_id_looking_for'] = $sessionCookie;
                                    $sessionData['session_id_length'] = strlen($sessionCookie);
                                }
                            } catch (\Exception $e) {
                                $sessionData['table_error'] = $e->getMessage();
                            }
                        }
                    } catch (\Exception $e) {
                        $sessionData['session_found_in_db'] = false;
                        $sessionData['session_error'] = $e->getMessage();
                    }
                    
                    // Now try to get user using UID from SSO session
                    // Konsep: UID dari SSO = pegawai_id di local users
                    // Pass request untuk validasi browser/client
                    $user = \App\Helpers\SsoSessionHelper::checkAndGetUser($sessionCookie, $request);
                    if ($user) {
                        $sessionData['database_user_found'] = true;
                        $sessionData['user_id'] = $user->id;
                        $sessionData['user_nama'] = $user->nama;
                        $sessionData['user_pegawai_id'] = $user->pegawai_id;
                        $sessionData['login_method'] = 'UID dari SSO = pegawai_id di local';
                        $userData = [
                            'id' => $user->id,
                            'nama' => $user->nama,
                            'user' => $user->user,
                            'pegawai_id' => $user->pegawai_id,
                        ];
                    } else {
                        $sessionData['database_user_found'] = false;
                        $sessionData['login_method'] = 'UID dari SSO = pegawai_id di local (user tidak ditemukan)';
                    }
                } catch (\Exception $e) {
                    $sessionData['database_error'] = $e->getMessage();
                    $sessionData['database_error_trace'] = substr($e->getTraceAsString(), 0, 500);
                }
            }
            
            // Try to fetch user info from SSO using cookies
            if ($sessionCookie) {
                try {
                    // Build cookie string
                    $cookieString = $cookieName . '=' . $sessionCookie;
                    
                    // Try multiple endpoints
                    $endpoints = [
                        'http://app.ptamgm.net/api/user',
                        'http://app.ptamgm.net/api/auth/user',
                        'http://app.ptamgm.net/api/me',
                        'http://app.ptamgm.net/user',
                    ];
                    
                    foreach ($endpoints as $endpoint) {
                        try {
                            $response = Http::withHeaders([
                                'Cookie' => $cookieString,
                            ])->timeout(3)->get($endpoint);
                            
                            if ($response->successful()) {
                                $userData = $response->json();
                                $sessionData['endpoint'] = $endpoint;
                                $sessionData['status'] = 'success';
                                break;
                            }
                        } catch (\Exception $e) {
                            // Continue to next endpoint
                        }
                    }
                    
                    // If no API endpoint works, try to get from SSO page directly
                    if (!$userData) {
                        try {
                            $response = Http::withHeaders([
                                'Cookie' => $cookieString,
                            ])->timeout(3)->get('http://app.ptamgm.net/');
                            
                            $sessionData['sso_page_status'] = $response->status();
                            $sessionData['sso_page_content_length'] = strlen($response->body());
                            
                            // Try to extract user info from HTML if possible
                            $body = $response->body();
                            if (preg_match('/user["\']?\s*[:=]\s*["\']?(\d+)/i', $body, $matches)) {
                                $sessionData['extracted_user'] = $matches[1];
                            }
                            if (preg_match('/nik["\']?\s*[:=]\s*["\']?(\d+)/i', $body, $matches)) {
                                $sessionData['extracted_nik'] = $matches[1];
                            }
                        } catch (\Exception $e) {
                            $sessionData['sso_page_error'] = $e->getMessage();
                        }
                    }
                    
                } catch (\Exception $e) {
                    $sessionData['api_error'] = $e->getMessage();
                }
            }
            
            // Also check query parameters and POST data
            $sessionData['query_params'] = $request->query();
            $sessionData['post_data'] = $request->post();
            $sessionData['headers'] = [
                'referer' => $request->header('referer'),
                'user-agent' => $request->header('user-agent'),
            ];
            
            return response()->json([
                'session_data' => $sessionData,
                'user_data' => $userData,
                'has_active_session' => !is_null($userData),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Auto-login from SSO session
     */
    public function autoLogin(Request $request)
    {
        try {
            // Get all cookies
            $allCookies = $request->cookies->all();
            $sessionCookie = null;
            $cookieName = null;
            
            foreach (['session', 'sso_session', 'laravel_session', 'PHPSESSID', 'session_id', 'app_session'] as $name) {
                if (isset($allCookies[$name])) {
                    $sessionCookie = $allCookies[$name];
                    $cookieName = $name;
                    break;
                }
            }
            
            $uid = $request->get('uid');
            $nik = $request->get('nik');
            
            // Try to get from SSO API
            if (!$uid && !$nik && $sessionCookie) {
                $cookieString = $cookieName . '=' . $sessionCookie;
                
                $endpoints = [
                    'http://app.ptamgm.net/api/user',
                    'http://app.ptamgm.net/api/auth/user',
                    'http://app.ptamgm.net/api/me',
                ];
                
                foreach ($endpoints as $endpoint) {
                    try {
                        $response = Http::withHeaders([
                            'Cookie' => $cookieString,
                        ])->timeout(3)->get($endpoint);
                        
                        if ($response->successful()) {
                            $userData = $response->json();
                            $uid = $userData['uid'] ?? $userData['user_id'] ?? $userData['id'] ?? null;
                            $nik = $userData['nik'] ?? $userData['nip'] ?? $userData['employee_id'] ?? $uid;
                            break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            $identifier = $nik ?? $uid;
            
            if (!$identifier) {
                return response()->json([
                    'success' => false,
                ], 400);
            }
            
            // Find user
            $user = null;
            $pegawai = Pegawai::where('nik', $identifier)->first();
            if ($pegawai) {
                $user = User::where('pegawai_id', $pegawai->id)->first();
            }
            
            if (!$user) {
                $user = User::where('user', $identifier)->first();
            }
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                ], 404);
            }
            
            Auth::login($user, true);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                ],
                'redirect_url' => url('/admin'),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store device fingerprint from client
     * Fingerprint digunakan untuk matching session yang lebih akurat
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDeviceFingerprint(Request $request)
    {
        try {
            $fingerprint = $request->input('fingerprint');
            
            if (!$fingerprint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint tidak ditemukan'
                ], 400);
            }
            
            // Simpan fingerprint di session
            // Ini akan digunakan untuk matching session SSO
            session(['device_fingerprint' => $fingerprint]);
            session(['device_fingerprint_timestamp' => now()->timestamp]);
            
            \Log::debug('SSO: Device fingerprint stored', [
                'fingerprint' => substr($fingerprint, 0, 20) . '...',
                'timestamp' => now()->toDateTimeString(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Fingerprint berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('SSO: Error storing device fingerprint', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternative callback method that reads session from SSO domain
     * This method tries to read session data by making a request to SSO
     */
    public function callbackWithSession(Request $request)
    {
        try {
            // Get session token or identifier from request
            $token = $request->get('token');
            $sessionId = $request->get('session_id');
            
            if (!$token && !$sessionId) {
                return redirect('/admin/login')
                    ->with('error', 'Token atau session ID tidak ditemukan.');
            }
            
            // Make request to SSO to verify and get user data
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Cookie' => 'session_id=' . $sessionId,
            ])->get('http://app.ptamgm.net/api/verify', [
                'token' => $token,
                'session_id' => $sessionId,
            ]);
            
            if (!$response->successful()) {
                return redirect('/admin/login')
                    ->with('error', 'Tidak dapat memverifikasi session dari SSO.');
            }
            
            $userData = $response->json();
            $nik = $userData['nik'] ?? $userData['uid'] ?? null;
            
            if (!$nik) {
                return redirect('/admin/login')
                    ->with('error', 'Data pengguna tidak ditemukan dalam response SSO.');
            }
            
            // Find and login user (same logic as callback method)
            $user = null;
            $pegawai = Pegawai::where('nik', $nik)->first();
            if ($pegawai) {
                $user = User::where('pegawai_id', $pegawai->id)->first();
            }
            
            if (!$user) {
                $user = User::where('user', $nik)->first();
            }
            
            if (!$user) {
                return redirect('/admin/login')
                    ->with('error', 'Pengguna dengan NIK ' . $nik . ' tidak ditemukan dalam sistem.');
            }
            
            Auth::login($user, true);
            
            $intendedUrl = session('sso_intended_url', url('/admin'));
            session()->forget('sso_intended_url');
            
            return redirect($intendedUrl)
                ->with('success', 'Login berhasil melalui SSO.');
                
        } catch (\Exception $e) {
            return redirect('/admin/login')
                ->with('error', 'Terjadi kesalahan saat proses login SSO.');
        }
    }
}
