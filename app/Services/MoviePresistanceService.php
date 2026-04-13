<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class MoviePresistanceService
{
    public function saveMovieFromApi(array $movieData, int $userId): void
    {
        $payload = $this->buildProcedurePayload($movieData, $userId);

        if ($payload === null) {
            return;
        }

        DB::statement('CALL populate_movie_from_api(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $payload);
    }

    public function saveMoviesFromApi(iterable $movies, int $userId): void
    {
        foreach ($movies as $movieData) {
            $this->saveMovieFromApi($movieData, $userId);
        }
    }

    private function buildProcedurePayload(array $movieData, int $userId): ?array
    {
        $title = $movieData['Title'] ?? $movieData['title'] ?? null;
        $imdbId = $movieData['imdbID'] ?? $movieData['imdbId'] ?? null;

        if ($title === null || $imdbId === null) {
            return null;
        }

        return [
            $title,
            $imdbId,
            $movieData['Year'] ?? $movieData['year'] ?? 'N/A',
            $movieData['Rated'] ?? $movieData['rated'] ?? 'N/A',
            $movieData['Runtime'] ?? $movieData['runtime'] ?? 'N/A',
            $movieData['Genre'] ?? $movieData['genre'] ?? 'N/A',
            $movieData['Actors'] ?? $movieData['actors'] ?? 'N/A',
            $movieData['Plot'] ?? $movieData['plot'] ?? 'N/A',
            $movieData['Poster'] ?? $movieData['poster'] ?? 'N/A',
            $movieData['Language'] ?? $movieData['language'] ?? $movieData['languages'] ?? 'N/A',
            $movieData['imdbRating'] ?? 'N/A',
            $userId,
        ];
    }
}
