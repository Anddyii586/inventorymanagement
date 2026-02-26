<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;
use Throwable;

class SsoConnectionService
{
    /**
     * Get SSO connection config, with support for testing overrides
     * 
     * @return array|null
     */
    protected static function getSsoConfig(): ?array
    {
        $config = config('database.connections.sso');
        
        // Allow override for testing via environment variables
        if (env('SSO_TEST_HOST')) {
            $config['host'] = env('SSO_TEST_HOST');
        }
        if (env('SSO_TEST_PORT')) {
            $config['port'] = env('SSO_TEST_PORT');
        }
        
        return $config;
    }

    /**
     * Check if SSO database connection is available
     * Uses a quick connection test with timeout protection
     * 
     * @param int $timeout Timeout in seconds (default: 2)
     * @return bool
     */
    public static function isAvailable(int $timeout = 2): bool
    {
        // Allow disabling SSO checks via environment variable (useful for local development)
        // Use filter_var to properly handle string 'false' from env variables
        if (filter_var(env('SSO_DISABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        // Store original timeout setting
        $originalTimeout = ini_get('max_execution_time');
        
        try {
            // Set execution time limit for this check
            @set_time_limit($timeout + 1);

            // First, try to check if we can reach the host/port quickly
            // This prevents PDO from hanging on connection attempts
            $config = self::getSsoConfig();
            if ($config && isset($config['host']) && isset($config['port'])) {
                // Use very short timeout (1 second) for socket check
                $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 1);
                if (!$connection) {
                    // Can't reach host/port, clear cache and return false immediately
                    Cache::forget('sso_connection_available');
                    if ($originalTimeout !== false && is_numeric($originalTimeout)) {
                        set_time_limit((int)$originalTimeout);
                    }
                    return false;
                }
                fclose($connection);
            }

            // Disconnect any existing SSO connection to force a fresh connection test
            try {
                DB::purge('sso');
            } catch (\Exception $e) {
                // Ignore purge errors
            }

            // Try a simple query with Laravel's DB facade
            // If testing mode, temporarily override config
            if (env('SSO_TEST_HOST') || env('SSO_TEST_PORT')) {
                $testConfig = self::getSsoConfig();
                $baseConfig = config('database.connections.sso') ?? [];
                config(['database.connections.sso' => array_merge($baseConfig, $testConfig)]);
            }
            
            $result = DB::connection('sso')->select('SELECT 1 as test');

            // Restore original timeout
            if ($originalTimeout !== false && is_numeric($originalTimeout)) {
                set_time_limit((int)$originalTimeout);
            }

            return !empty($result);
        } catch (Exception $e) {
            // Log the error for debugging (only in local environment)
            if (app()->environment('local')) {
                Log::debug('SSO connection unavailable: ' . $e->getMessage());
            }
            
            // Clear cache on failure to ensure next check is fresh
            Cache::forget('sso_connection_available');

            // Restore original timeout
            if ($originalTimeout !== false && is_numeric($originalTimeout)) {
                set_time_limit((int)$originalTimeout);
            }

            return false;
        } catch (Throwable $e) {
            // Catch any other errors including fatal errors
            if (app()->environment('local')) {
                Log::debug('SSO connection unavailable: ' . $e->getMessage());
            }

            // Clear cache on failure to ensure next check is fresh
            Cache::forget('sso_connection_available');

            if ($originalTimeout !== false && is_numeric($originalTimeout)) {
                set_time_limit((int)$originalTimeout);
            }
            
            return false;
        }
    }

    /**
     * Get cached availability status
     * Uses cache to avoid checking on every request
     * 
     * @param int $cacheMinutes Cache duration in minutes (default: 1)
     * @return bool
     */
    public static function isAvailableCached(int $cacheMinutes = 1): bool
    {
        try {
            $cacheKey = 'sso_connection_available';
            
            // Check if we have a cached value
            if (cache()->has($cacheKey)) {
                $cached = cache()->get($cacheKey);
                // Ensure we return boolean
                return $cached === true || $cached === '1' || $cached === 1;
            }
            
            // If not cached, check availability and cache the result
            $available = self::isAvailable();
            
            // Only cache if available is true, to ensure quick recovery when connection is restored
            // If false, don't cache (or cache for shorter time) so next check happens sooner
            if ($available) {
                cache()->put($cacheKey, $available, now()->addMinutes($cacheMinutes));
            } else {
                // Cache false for shorter time (30 seconds) so we check again soon
                cache()->put($cacheKey, $available, now()->addSeconds(30));
            }
            
            return $available;
        } catch (\Exception $e) {
            // If cache fails, try direct check but return false on any error
            try {
                return self::isAvailable();
            } catch (\Throwable $e) {
                // If everything fails, assume SSO is not available
                return false;
            }
        }
    }
}
