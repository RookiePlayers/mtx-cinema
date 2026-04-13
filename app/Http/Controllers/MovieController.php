<?php

namespace App\Http\Controllers;

use App\Jobs\FetchAnDumpMoviesJob;
use App\Models\PaginationInput;
use App\Services\DBService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function __construct(
        private readonly DBService $dbService,
    ) {
    }

    public function fetchMoviesFromApi()
    {
        // This method can be used to trigger the movie fetching process manually if needed.
        app()->make(FetchAnDumpMoviesJob::class)->dispatch();
        return response()->json(['message' => 'Movie fetching job dispatched.']);
    }
    public function show(Request $request)
    {
        $id = $request->route('id');
        $userId = $request->user()?->id;
        $existingGuestToken = $request->cookie('guest_token');
        $guestToken = $userId === null
            ? ($existingGuestToken ?? Str::uuid()->toString())
            : null;
        $movie = $this->dbService->getMovieById($id, $userId, $guestToken);
        $isSaved = $movie !== null && $userId !== null
            ? $this->dbService->isMovieSavedForUser($movie->imdbId, $userId)
            : false;


            $response = Inertia::render('Movie', [
                'movie' => $movie,
                'movieId' => $id,
                'notFound' => $movie === null,
                'isSaved' => $isSaved,
            ])->toResponse($request);

            if ($guestToken !== null && $existingGuestToken === null) {
                $response->headers->setCookie(cookie('guest_token', $guestToken, 60 * 24 * 30));
            }

            return $movie
                ? $response
                : $response->setStatusCode(404);
    }

    public function save(Request $request, string $id)
    {
        $userId = $request->user()?->id;

        abort_unless($userId !== null, 403);

        $movie = $this->dbService->saveMovieForUser($id, $userId);

        abort_if($movie === null, 404, 'Movie not found');

        return redirect()->route('movie', ['id' => $id]);
    }

    public function unsave(Request $request, string $id)
    {
        $userId = $request->user()?->id;

        abort_unless($userId !== null, 403);

        $this->dbService->removeSavedMovieForUser($id, $userId);

        return redirect()->route('movie', ['id' => $id]);
    }
}
