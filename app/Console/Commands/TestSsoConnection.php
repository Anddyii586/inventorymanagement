<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SsoConnectionService;
use Illuminate\Support\Facades\Cache;

class TestSsoConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:test 
                            {--force : Force refresh cache}
                            {--test-fail : Test with random IP to simulate connection failure}
                            {--test-real : Test with real SSO IP (default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SSO database connection and show status. Use --test-fail to simulate connection failure.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing SSO connection...');
        
        // Handle test scenarios
        if ($this->option('test-fail')) {
            // Generate random IP for testing failure scenario
            $randomIp = $this->generateRandomIp();
            $this->warn("🧪 TEST MODE: Using random IP to simulate connection failure");
            $this->info("Setting SSO_TEST_HOST={$randomIp}");
            putenv("SSO_TEST_HOST={$randomIp}");
            $_ENV['SSO_TEST_HOST'] = $randomIp;
        } elseif ($this->option('test-real')) {
            // Use real IP
            $this->info("✅ Using real SSO configuration");
            putenv('SSO_TEST_HOST');
            putenv('SSO_TEST_PORT');
            unset($_ENV['SSO_TEST_HOST']);
            unset($_ENV['SSO_TEST_PORT']);
        }
        
        // Clear cache if force flag is set
        if ($this->option('force')) {
            Cache::forget('sso_connection_available');
            $this->info('Cache cleared.');
        }
        
        // Test connection
        $this->info('Checking socket connectivity...');
        $config = $this->getTestConfig();
        $this->info("Using host: {$config['host']}:{$config['port']}");
        
        if ($config && isset($config['host']) && isset($config['port'])) {
            $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 2);
            if ($connection) {
                fclose($connection);
                $this->info("✓ Socket connection to {$config['host']}:{$config['port']} successful");
            } else {
                $this->error("✗ Socket connection to {$config['host']}:{$config['port']} failed: $errstr ($errno)");
                $this->warn('This might be normal if you are developing locally without VPN.');
            }
        }
        
        $this->info('Testing database connection...');
        $available = SsoConnectionService::isAvailable();
        
        if ($available) {
            $this->info('✓ SSO connection is AVAILABLE');
            $this->info('SSO features should work normally.');
        } else {
            $this->error('✗ SSO connection is NOT AVAILABLE');
            $this->warn('SSO features will be disabled gracefully.');
        }
        
        // Show cached status
        $cached = Cache::get('sso_connection_available');
        $cachedStatus = ($cached === true || $cached === '1' || $cached === 1) ? 'AVAILABLE' : 'NOT AVAILABLE';
        $this->info("\nCached status: " . $cachedStatus);
        $this->info("Cache value: " . var_export($cached, true));
        
        // Check environment variable
        if (env('SSO_DISABLED', false)) {
            $this->warn("\n⚠ SSO_DISABLED is set to true in .env file.");
            $this->warn('Set SSO_DISABLED=false or remove it to enable SSO checks.');
        }
        
        // Show test mode info
        if ($this->option('test-fail')) {
            $this->newLine();
            $this->info('💡 To test with real IP, run: php artisan sso:test --test-real --force');
        }
        
        return $available ? 0 : 1;
    }
    
    /**
     * Get test configuration (with overrides if in test mode)
     */
    protected function getTestConfig(): array
    {
        $config = config('database.connections.sso');
        
        if (env('SSO_TEST_HOST')) {
            $config['host'] = env('SSO_TEST_HOST');
        }
        if (env('SSO_TEST_PORT')) {
            $config['port'] = env('SSO_TEST_PORT');
        }
        
        return $config;
    }
    
    /**
     * Generate random IP for testing
     */
    protected function generateRandomIp(): string
    {
        // Generate random private IP that likely won't be reachable
        return '192.168.' . rand(200, 255) . '.' . rand(200, 255);
    }
}

