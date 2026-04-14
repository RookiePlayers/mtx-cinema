<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\PaginationData;
use App\Models\PaginationInput;
use App\Models\UserSavedMovie;
use App\Models\UserSearch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DBService
{
    private const MERGED_SEARCH_CACHE_TTL_SECONDS = 300;
    private const MIN_MERGED_SEARCH_WINDOW = 20;
    private const MAX_API_PAGES_PER_SEARCH = 1;

    public function __construct(
        private readonly OMDBApiService $omdbApiService,
        private readonly MoviePresistanceService $moviePresistanceService,
    ) {
    }
    public function searchMovie(string $query, ?int $userId, ?string $guestToken, PaginationInput $pagination): PaginationData
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return $this->searchLocalOnly($pagination, $userId, $guestToken);
        }

        $requiredWindow = max(
            $pagination->offset + $pagination->limit,
            self::MIN_MERGED_SEARCH_WINDOW
        );

        $cacheKey = $this->buildMergedSearchCacheKey($normalizedQuery);
        $cached = Cache::get($cacheKey);

        if (
            ! is_array($cached)
            || ! isset($cached['results'], $cached['window'])
            || $cached['window'] < $requiredWindow
        ) {
            $cached = $this->buildMergedSearchCacheValue($normalizedQuery, $requiredWindow);
            Cache::put($cacheKey, $cached, self::MERGED_SEARCH_CACHE_TTL_SECONDS);
        }

        $mergedResults = $cached['results'];
        $pageResults = array_slice($mergedResults, $pagination->offset, $pagination->limit);

        $resultCollection = collect($pageResults);
        $this->recordSearch($userId, $guestToken, $normalizedQuery, $resultCollection);

        $count = count($pageResults);
        $nextOffset = $pagination->offset + $count;
        $hasMore = $nextOffset < $cached['total'];

        return new PaginationData(
            $pageResults,
            $hasMore ? (string) $nextOffset : null,
            $cached['total'],
            $count,
            $hasMore,
        );
    }

    public function getMovieById(string $id, ?int $userId, ?string $guestToken): ?Movie
    {
        $movie = Movie::query()->where('imdbId', $id)->first();
        if (! $movie) {
            $apiData = $this->omdbApiService->fetchMovieDetailsFromApi($id);
            $movie = $apiData ? new Movie([
                'title' => $apiData['Title'] ?? null,
                'imdbId' => $apiData['imdbID'] ?? $apiData['imdbId'] ?? null,
                'year' => $apiData['Year'] ?? null,
                'rated' => $apiData['Rated'] ?? null,
                'runtime' => $apiData['Runtime'] ?? null,
                'genre' => $apiData['Genre'] ?? null,
                'actors' => $apiData['Actors'] ?? null,
                'plot' => $apiData['Plot'] ?? null,
                'poster' => $apiData['Poster'] ?? null,
                'languages' => $apiData['Language'] ?? $apiData['languages'] ?? null,
                'imdbRating' => $apiData['imdbRating'] ?? null,
            ]) : null;

            if ($apiData && isset($apiData['imdbID']) && $userId) {
                $this->moviePresistanceService->saveMovieFromApi($apiData, $userId ?? 0);
            }
        }

        return $movie;
    }

    private function searchLocalOnly(PaginationInput $pagination, ?int $userId, ?string $guestToken): PaginationData
    {
        $baseQuery = Movie::query()->orderBy('title');
        $total = (clone $baseQuery)->reorder()->count();
        $movies = (clone $baseQuery)
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->get();

        $count = $movies->count();
        $nextOffset = $pagination->offset + $count;
        $hasMore = $nextOffset < $total;

        $this->recordSearch($userId, $guestToken, '', $movies);

        return new PaginationData(
            $movies->toArray(),
            $hasMore ? (string) $nextOffset : null,
            $total,
            $count,
            $hasMore,
        );
    }

    // This method generates a cache key that is unique per search query and user/guest context.
    private function buildMergedSearchCacheKey(string $query): string
    {
        return 'search:merged:'.sha1(Str::lower($query));
    }

    // This method performs the merged search logic, combining local database results with API results, and ranking them.
    private function buildMergedSearchCacheValue(string $query, int $window): array
    {
        $localSearch = $this->searchLocalMovies($query, $window);
        $apiSearch = $localSearch['total'] >= $window
            ? ['results' => [], 'total' => 0]
            : $this->searchApiMovies($query, $window);
        $mergedResults = $this->mergeAndRankMovieResults(
            $query,
            $localSearch['results'],
            $apiSearch['results'],
        );

        return [
            'window' => $window,
            'total' => count($mergedResults),
            'results' => $mergedResults,
        ];
    }

    private function searchLocalMovies(string $query, int $window): array
    {
        $baseQuery = Movie::query()->searchFast($query);

        return [
            'results' => (clone $baseQuery)
                ->limit($window)
                ->get()
                ->map(function (Movie $movie): array {
                    $result = $movie->toArray();
                    $result['_source'] = 'db';

                    return $result;
                })
                ->all(),
            'total' => (clone $baseQuery)->reorder()->count(),
        ];
    }

    private function searchApiMovies(string $query, int $window): array
    {
        $perPage = 10;
        $pagesToFetch = min(
            self::MAX_API_PAGES_PER_SEARCH,
            max(1, (int) ceil($window / $perPage))
        );

        $results = [];
        $total = 0;

        for ($page = 1; $page <= $pagesToFetch; $page++) {
            $pagination = new PaginationInput(
                null,
                $perPage,
                ($page - 1) * $perPage,
            );

            try {
                $apiData = $this->omdbApiService->searchMoviesFromApi($query, $pagination);
            } catch (\Throwable $exception) {
                Log::warning('Merged search API fetch failed.', [
                    'query' => $query,
                    'page' => $page,
                    'exception' => $exception->getMessage(),
                ]);

                break;
            }

            $total = max($total, $apiData->total ?? 0);

            foreach ($apiData->data as $movie) {
                $movie['_source'] = 'api';
                $results[] = $movie;
            }

            if (! $apiData->hasMore) {
                break;
            }
        }

        return [
            'results' => $results,
            'total' => $total,
        ];
    }

    private function dedupeAndMergeMoviesByImdbId(array $moviesListA, array $moviesListB = []): array
    {
        $deduped = [];
        foreach ($moviesListA as $movie) {
            $imdbId = $movie['imdbId'] ?? null;

            if (! is_string($imdbId) || $imdbId === '') {
                continue;
            }

            $deduped[$imdbId] = $movie;
        }
        foreach ($moviesListB as $movie) {
            $imdbId = $movie['imdbId'] ?? null;

            if (! is_string($imdbId) || $imdbId === '') {
                continue;
            }

            if (isset($deduped[$imdbId])) {
                $deduped[$imdbId] = array_merge($movie, $deduped[$imdbId]);
                $deduped[$imdbId]['_source'] = 'db';

                continue;
            }

            $deduped[$imdbId] = $movie;
        }

        return array_values($deduped);
    }

    // This method merges local and API search results,
    // We will first create a deduped list of movies based on imdbId,
    // then we will score each movie based on relevance to the search query and source (local vs API),
    // for example. query = "Spiderman", a movie with title "Spiderman" will score higher than "Spiderman 2",
    // and a movie that is present in the local database will score higher than one that is only from the API.
    // this helps with our ranking.
    private function mergeAndRankMovieResults(string $query, array $localResults, array $apiResults): array
    {
        $merged = $this->dedupeAndMergeMoviesByImdbId($localResults, $apiResults);

        $normalizedQuery = Str::lower(trim($query));
        $queryTokens = collect(explode(' ', $normalizedQuery))
            ->map(fn (string $token) => trim($token))
            ->filter()
            ->values()
            ->all();

        uasort($merged, function (array $left, array $right) use ($normalizedQuery, $queryTokens): int {
            $leftScore = $this->scoreMovieSearchResult($left, $normalizedQuery, $queryTokens);
            $rightScore = $this->scoreMovieSearchResult($right, $normalizedQuery, $queryTokens);

            if ($leftScore !== $rightScore) {
                return $rightScore <=> $leftScore;
            }

            //same score trying imdb rating, higher is better
            $leftRating = (float) ($left['imdbRating'] ?? 0);
            $rightRating = (float) ($right['imdbRating'] ?? 0);

            if ($leftRating !== $rightRating) {
                return $rightRating <=> $leftRating;
            }
            //same score and rating, sort alphabetically by title
            return strcmp(
                (string) ($left['title'] ?? ''),
                (string) ($right['title'] ?? ''),
            );
        });

        return array_values(array_map(function (array $movie): array {
            unset($movie['_source']);

            return $movie;
        }, $merged));
    }

    private function scoreMovieSearchResult(array $movie, string $query, array $queryTokens): int
    {
        $title = Str::lower((string) ($movie['title'] ?? ''));
        $genre = Str::lower((string) ($movie['genre'] ?? ''));
        $actors = Str::lower((string) ($movie['actors'] ?? ''));
        $source = $movie['_source'] ?? 'api';
        $score = 0;

        if ($title === $query) {
            $score += 100;
        }

        if ($title !== '' && str_starts_with($title, $query)) {
            $score += 60;
        }

        foreach ($queryTokens as $token) {
            if ($token === '') {
                continue;
            }

            if (str_contains($title, $token)) {
                $score += 20;
            }

            if (str_contains($genre, $token)) {
                $score += 8;
            }

            if (str_contains($actors, $token)) {
                $score += 5;
            }
        }

        if ($source === 'db') {
            $score += 10;
        }

        return $score;
    }

    public function getSavedMovies(int $userId, string $scope = 'mine'): Collection
    {
        $query = Movie::query()
            ->select([
                'movies.*',
                'users.name as savedByUserName',
                'user_saved_movies.created_at as savedAt',
            ])
            ->join('user_saved_movies', 'user_saved_movies.movie_id', '=', 'movies.id')
            ->join('users', 'users.id', '=', 'user_saved_movies.user_id')
            ->orderByDesc('user_saved_movies.created_at');

        if ($scope !== 'all') {
            $query->where('user_saved_movies.user_id', $userId);
        }

        return $query
            ->get();
    }

    public function isMovieSavedForUser(string $imdbId, int $userId): bool
    {
        return UserSavedMovie::query()
            ->where('user_id', $userId)
            ->whereHas('movie', fn ($query) => $query->where('imdbId', $imdbId))
            ->exists();
    }

    public function saveMovieForUser(string $imdbId, int $userId): ?Movie
    {
        $movie = $this->getMovieById($imdbId, $userId, null);

        if (! $movie || ! $movie->exists) {
            $movie = Movie::query()->where('imdbId', $imdbId)->first();
        }

        if (! $movie) {
            return null;
        }

        UserSavedMovie::query()->firstOrCreate([
            'user_id' => $userId,
            'movie_id' => $movie->id,
        ]);

        return $movie;
    }

    public function removeSavedMovieForUser(string $imdbId, int $userId): void
    {
        $movieId = Movie::query()
            ->where('imdbId', $imdbId)
            ->value('id');

        if ($movieId === null) {
            return;
        }

        UserSavedMovie::query()
            ->where('user_id', $userId)
            ->where('movie_id', $movieId)
            ->delete();
    }

    // This method records the search term and the first movie result for analytics purposes.
    private function recordSearch(?int $userId, ?string $guestToken, string $query, Collection $movies): void
    {
        if (($userId === null && $guestToken === null) || $query === '' || $movies->isEmpty()) {
            return;
        }

        $movieId = $movies
            ->map(function ($movie) {
                if (is_array($movie)) {
                    return $movie['id'] ?? null;
                }

                return $movie->id ?? null;
            })
            ->first(fn ($id) => $id !== null);

        if ($movieId === null) {
            return;
        }

        UserSearch::query()->create([
            'user_id' => $userId,
            'guest_token' => $guestToken,
            'movie_id' => $movieId,
            'searchTerm' => $query,
        ]);
    }

    public function upsertMovie(array $movieData, ?int $userId): ?Movie
    {
        $imdbId = $movieData['imdbId'] ?? null;

        if (! is_string($imdbId) || $imdbId === '') {
            $imdbId = 'local-'.Str::uuid()->toString();
        }

        $movieData['imdbId'] = $imdbId;
        $this->moviePresistanceService->saveMovieFromApi($movieData, $userId);

        return Movie::query()->where('imdbId', $imdbId)->first();
    }
    public function deleteMovieByImdbId(string $imdbId): void
    {
        Movie::query()->where('imdbId', $imdbId)->delete();
    }

    public function updateMovieByImdbId(string $imdbId, array $movieData, ?int $userId): ?Movie
    {
        $movie = Movie::query()->where('imdbId', $imdbId)->first();

        if (! $movie) {
            return null;
        }

        $payload = [
            'title' => $movieData['title'] ?? $movie->title,
            'imdbId' => $movie->imdbId,
            'year' => $movieData['year'] ?? $movie->year,
            'rated' => $movieData['rated'] ?? $movie->rated,
            'runtime' => $movieData['runtime'] ?? $movie->runtime,
            'genre' => $movieData['genre'] ?? $movie->genre,
            'actors' => $movieData['actors'] ?? $movie->actors,
            'plot' => $movieData['plot'] ?? $movie->plot,
            'poster' => $movieData['poster'] ?? $movie->poster,
            'languages' => $movieData['languages'] ?? $movie->languages,
            'imdbRating' => $movieData['imdbRating'] ?? $movie->imdbRating,
        ];

        $this->moviePresistanceService->saveMovieFromApi($payload, $userId ?? 0);

        return Movie::query()->where('imdbId', $imdbId)->first();
    }
}
