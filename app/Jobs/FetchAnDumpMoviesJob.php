<?php

namespace App\Jobs;

use App\Services\MovieDumpFetchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchAnDumpMoviesJob implements ShouldQueue
{
    use Queueable;

    public $retries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(MovieDumpFetchService $service): void
    {
        Log::info('Starting FetchAnDumpMoviesJob');
        $service->fetchMovies();
        Log::info('Finished FetchAnDumpMoviesJob');
    }
}
