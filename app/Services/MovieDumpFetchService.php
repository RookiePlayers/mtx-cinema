<?php
namespace App\Services;

use App\Models\ExternalPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MovieDumpFetchService
{
    public function fetchMovies()
    {
         $response = Http::timeout(30)->get(config('services.movie_api.base_url'), [
            'apikey' => config('services.movie_api.key'),
        ]);

        if ($response->successful()) {
            $movies = $response->json();

            foreach ($movies as $movie) {
                DB::statement('CALL populate_movie_from_api(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                    $movie['Title'] ?? null,
                    $movie['imdbID'] ?? null,
                    $movie['Year'] ?? null,
                    $movie['Rated'] ?? null,
                    $movie['Runtime'] ?? null,
                    $movie['Genre'] ?? null,
                    $movie['Actors'] ?? null,
                    $movie['Plot'] ?? null,
                    $movie['Poster'] ?? null,
                    $movie['Language'] ?? null,
                    $movie['imdbRating'] ?? null,
                ]);
            }
        } else {
            Log::error('Failed to fetch movies from API', ['response' => $response->body()]);
        }
    }
}
