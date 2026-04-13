<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\PaginationData;
use App\Models\PaginationInput;
use App\Utils\Normalization;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OMDBApiService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.movie_api.key');
        $this->baseUrl = config('services.movie_api.base_url');
    }

    public function searchMoviesFromApi(string $query, PaginationInput $pagination): PaginationData
    {
        $response = Http::timeout(30)->get($this->baseUrl, [
            'apikey' => $this->apiKey,
            's' => $query,
            'page' => max(1, intdiv($pagination->offset, $pagination->limit) + 1),
        ]);

        $data = $response->json();

        if (! $response->successful() || ($data['Response'] ?? 'True') === 'False') {
            throw new Exception('OMDB API error: '.($data['Error'] ?? $response->body() ?? 'Unknown error'));
        }

        $searchResults = array_map(
            fn (array $movie) => Normalization::normalizeKeysFromOMDBApi($movie),
            $data['Search'] ?? []
        );
        Log::info('OMDB API search results', ['query' => $query, 'results_count' => count($searchResults)]);
        $totalResults = isset($data['totalResults']) ? (int) $data['totalResults'] : 0;
        $count = count($searchResults);
        $hasMore = ($pagination->offset + $count) < $totalResults;

        return new PaginationData(
            $searchResults,
            $hasMore ? (string) ($pagination->offset + $count) : null,
            $totalResults,
            $count,
            $hasMore
        );
    }
    public function fetchMovieDetailsFromApi(string $imdbId): ?array
    {
        $response = Http::timeout(30)->get($this->baseUrl, [
            'apikey' => $this->apiKey,
            'i' => $imdbId,
        ]);

        $data = $response->json();
        Log::info('OMDB API fetch movie details', ['imdbId' => $imdbId, 'response' => $data]);

        if (! $response->successful() || ($data['Response'] ?? 'True') === 'False') {
            Log::error('OMDB API error fetching movie details', ['imdbId' => $imdbId, 'error' => ($data['Error'] ?? $response->body() ?? 'Unknown error')]);
            return null;
        }

        $movie = Normalization::normalizeKeysFromOMDBApi($data);
        return $movie;
    }

}
