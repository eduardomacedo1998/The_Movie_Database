<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\TmdbService;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-expired {--force : Force clear all cache without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired cache and rebuild essential cache data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will clear all Redis cache. Continue?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Clearing expired cache...');

        // Limpar cache Redis
        Cache::store('redis')->flush();
        $this->info('✓ Redis cache cleared');

        // Reconstruir cache essencial
        $this->info('Rebuilding essential cache...');
        $tmdbService = app(TmdbService::class);
        $tmdbService->getGenres();
        $this->info('✓ Genres cache rebuilt');

        // Limpar caches Laravel
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        $this->info('✓ All expired cache cleared and essential cache rebuilt successfully!');

        return Command::SUCCESS;
    }
}
