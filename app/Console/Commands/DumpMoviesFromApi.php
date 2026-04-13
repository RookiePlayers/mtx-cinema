<?php

namespace App\Console\Commands;

use App\Jobs\FetchAnDumpMoviesJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:dump-movies-from-api')]
#[Description('Command description')]
class DumpMoviesFromApi extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Running DumpMoviesFromApi command...");
        app()->make(FetchAnDumpMoviesJob::class)->dispatch();
    }
}
