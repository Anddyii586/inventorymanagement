<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateDepreciation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:calculate-depreciation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate depreciation for all assets based on straight line method';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting depreciation calculation...");
        
        $models = [
            \App\Models\PeralatanMesin::class,
            \App\Models\GedungBangunan::class,
            \App\Models\Jaringan::class,
            \App\Models\AsetTetapLainnya::class,
        ];

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);
            $this->info("Processing {$modelName}...");
            
            $query = $modelClass::query();
            $total = $query->count();
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $query->chunk(100, function ($assets) use ($bar) {
                foreach ($assets as $asset) {
                    try {
                        if (method_exists($asset, 'calculateDepreciation')) {
                            $asset->calculateDepreciation();
                        }
                    } catch (\Exception $e) {
                         Log::error("Failed to calculate depreciation for asset {$asset->id}: " . $e->getMessage());
                    }
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();
        }

        $this->info("Depreciation calculation completed.");
    }
}
