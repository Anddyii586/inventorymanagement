<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SsoConnectionService;

class ListSsoSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:sessions 
                            {--limit=50 : Limit number of sessions to show}
                            {--minutes=30 : Show sessions active in last N minutes}
                            {--user-agent= : Filter by user agent (partial match)}
                            {--uid= : Filter by user UID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List active SSO sessions with user information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check SSO connection first
        if (!SsoConnectionService::isAvailable()) {
            $this->error('✗ SSO connection is not available');
            $this->warn('Make sure SSO database is accessible.');
            return 1;
        }

        $this->info('Fetching active SSO sessions...');
        
        $limit = (int) $this->option('limit');
        $minutes = (int) $this->option('minutes');
        $userAgentFilter = $this->option('user-agent');
        $uidFilter = $this->option('uid');

        try {
            $connection = 'sso';
            $ssoSessionTable = 'sessions';
            
            // Build query
            $query = DB::connection($connection)->table($ssoSessionTable)
                ->where('last_activity', '>', now()->subMinutes($minutes)->timestamp)
                ->orderBy('last_activity', 'desc')
                ->limit($limit);

            // Try to get with user_agent if column exists
            $hasUserAgentColumn = false;
            try {
                $sessions = $query->get(['id', 'payload', 'user_agent', 'last_activity']);
                $hasUserAgentColumn = true;
            } catch (\Exception $e) {
                // If user_agent column doesn't exist, get without it
                $sessions = $query->get(['id', 'payload', 'last_activity']);
            }

            if ($sessions->isEmpty()) {
                $this->warn("No active sessions found in the last {$minutes} minutes.");
                return 0;
            }

            $this->info("Found {$sessions->count()} active session(s) in the last {$minutes} minutes:");
            $this->newLine();

            // Process sessions
            $tableData = [];
            $processedCount = 0;

            foreach ($sessions as $session) {
                try {
                    // Decode session payload
                    $sessionData = null;
                    try {
                        $decoded = base64_decode($session->payload);
                        $sessionData = @unserialize($decoded);
                        if ($sessionData === false) {
                            $sessionData = @unserialize($session->payload);
                        }
                    } catch (\Exception $e) {
                        // Skip if can't decode
                        continue;
                    }

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

                    // Apply filters
                    if ($uidFilter && $uid != $uidFilter) {
                        continue;
                    }

                    $sessionUserAgent = $hasUserAgentColumn ? ($session->user_agent ?? null) : null;
                    
                    if ($userAgentFilter && $sessionUserAgent) {
                        if (stripos($sessionUserAgent, $userAgentFilter) === false) {
                            continue;
                        }
                    }

                    // Get user info from SSO
                    $ssoUser = null;
                    try {
                        $ssoUser = DB::connection($connection)->table('pengguna')
                            ->where('id', $uid)
                            ->first(['id', 'uid', 'nama', 'pegawai_id']);
                    } catch (\Exception $e) {
                        // Skip if can't get user - this means session is not active
                        continue;
                    }

                    // Only show active sessions - must have valid user in SSO
                    if (!$ssoUser) {
                        continue;
                    }

                    // Format last activity
                    $lastActivity = $session->last_activity ?? null;
                    $lastActivityFormatted = 'N/A';
                    if ($lastActivity) {
                        $timestamp = is_numeric($lastActivity) ? $lastActivity : strtotime($lastActivity);
                        if ($timestamp) {
                            $lastActivityFormatted = date('Y-m-d H:i:s', $timestamp);
                            $diffMinutes = round((time() - $timestamp) / 60);
                            $lastActivityFormatted .= " ({$diffMinutes} min ago)";
                        }
                    }

                    // Get local user if exists
                    $localUser = null;
                    if ($ssoUser && $ssoUser->pegawai_id) {
                        try {
                            $localUser = \App\Models\User::where('pegawai_id', $ssoUser->pegawai_id)->first();
                        } catch (\Exception $e) {
                            // Skip if can't get local user
                        }
                    }

                    $tableData[] = [
                        'Session ID' => substr($session->id, 0, 20) . '...',
                        'UID' => $uid,
                        'Nama (SSO)' => $ssoUser->nama ?? 'N/A',
                        'Pegawai ID' => $ssoUser->pegawai_id ?? 'N/A',
                        'Local User' => $localUser ? $localUser->nama : 'Not found',
                        'User Agent' => $sessionUserAgent ? substr($sessionUserAgent, 0, 50) . '...' : 'N/A',
                        'Last Activity' => $lastActivityFormatted,
                    ];

                    $processedCount++;
                } catch (\Exception $e) {
                    // Skip this session if there's an error
                    continue;
                }
            }

            if (empty($tableData)) {
                $this->warn('No active sessions found matching the criteria.');
                $this->info('Note: Only sessions with valid UID and user in SSO are shown.');
                return 0;
            }

            // Display table
            $this->table(
                ['Session ID', 'UID', 'Nama (SSO)', 'Pegawai ID', 'Local User', 'User Agent', 'Last Activity'],
                $tableData
            );

            $this->newLine();
            $this->info("Total: {$processedCount} active session(s) displayed");

            // Show summary
            $this->newLine();
            $this->info('Summary:');
            $this->line("  - Total sessions checked: {$sessions->count()}");
            $this->line("  - Active sessions (with valid user): {$processedCount}");
            $this->line("  - Sessions with local user: " . count(array_filter($tableData, fn($row) => $row['Local User'] !== 'Not found')));
            $this->line("  - Sessions without local user: " . count(array_filter($tableData, fn($row) => $row['Local User'] === 'Not found')));

            return 0;
        } catch (\PDOException $e) {
            $this->error('✗ Database connection error: ' . $e->getMessage());
            return 1;
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            Log::error('SSO Sessions Command Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}

