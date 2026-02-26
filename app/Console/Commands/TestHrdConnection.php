<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HrdConnectionService;
use Illuminate\Support\Facades\Cache;

class TestHrdConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrd:test 
                            {--force : Force refresh cache}
                            {--test-fail : Test with random IP to simulate connection failure}
                            {--test-real : Test with real HRD IP (default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test HRD database connection and show status. Use --test-fail to simulate connection failure.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing HRD connection...');
        
        // Handle test scenarios
        if ($this->option('test-fail')) {
            // Generate random IP for testing failure scenario
            $randomIp = $this->generateRandomIp();
            $this->warn("🧪 TEST MODE: Using random IP to simulate connection failure");
            $this->info("Setting HRD_TEST_HOST={$randomIp}");
            putenv("HRD_TEST_HOST={$randomIp}");
            $_ENV['HRD_TEST_HOST'] = $randomIp;
        } elseif ($this->option('test-real')) {
            // Use real IP
            $this->info("✅ Using real HRD configuration");
            putenv('HRD_TEST_HOST');
            putenv('HRD_TEST_PORT');
            unset($_ENV['HRD_TEST_HOST']);
            unset($_ENV['HRD_TEST_PORT']);
        }
        
        // Clear cache if force flag is set
        if ($this->option('force')) {
            Cache::forget('hrd_connection_available');
            $this->info('Cache cleared.');
        }
        
        // Test connection
        $this->info('Checking socket connectivity...');
        $config = $this->getTestConfig();
        
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
        $available = HrdConnectionService::isAvailable();
        
        if ($available) {
            $this->info('✓ HRD connection is AVAILABLE');
            $this->info('Feature should be enabled in UserResource.');
        } else {
            $this->error('✗ HRD connection is NOT AVAILABLE');
            $this->warn('Feature will be disabled in UserResource.');
        }
        
        // Show cached status
        $cached = Cache::get('hrd_connection_available');
        $cachedStatus = ($cached === true || $cached === '1' || $cached === 1) ? 'AVAILABLE' : 'NOT AVAILABLE';
        $this->info("\nCached status: " . $cachedStatus);
        $this->info("Cache value: " . var_export($cached, true));
        
        // Check environment variable
        if (env('HRD_DISABLED', false)) {
            $this->warn("\n⚠ HRD_DISABLED is set to true in .env file.");
            $this->warn('Set HRD_DISABLED=false or remove it to enable HRD checks.');
        }
        
        // Show test mode info
        if ($this->option('test-fail')) {
            $this->newLine();
            $this->info('💡 To test with real IP, run: php artisan hrd:test --test-real --force');
        }
        
        return $available ? 0 : 1;
    }
    
    /**
     * Get test configuration (with overrides if in test mode)
     */
    protected function getTestConfig(): array
    {
        $config = config('database.connections.hrd');
        
        if (env('HRD_TEST_HOST')) {
            $config['host'] = env('HRD_TEST_HOST');
        }
        if (env('HRD_TEST_PORT')) {
            $config['port'] = env('HRD_TEST_PORT');
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

