<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClearExpiredCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Limpar cache antigo de TMDB (mais de 24h)
        \Illuminate\Support\Facades\Cache::store('redis')->flush();

        // Reconstruir cache de gêneros
        $tmdbService = app(\App\Services\TmdbService::class);
        $tmdbService->getGenres();

        \Illuminate\Support\Facades\Log::info('Cache expired cleared and rebuilt successfully');
    }
}
