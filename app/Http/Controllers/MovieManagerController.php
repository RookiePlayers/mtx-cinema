<?php

namespace App\Http\Controllers;

use App\Services\DBService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovieManagerController extends Controller
{
    public function __construct(
        private readonly DBService $dbService,
    )
    {
        // This constructor can be used to apply middleware or perform any setup needed for user-related actions.
    }

    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $scope = $request->string('scope')->toString();
        $scope = in_array($scope, ['mine', 'all'], true) ? $scope : 'mine';

        abort_unless($userId !== null, 403);

        return Inertia::render('MovieManager', [
        ]);
    }
    public function createMovie(Request $request)
    {
        $movieData = $request->validate([
            'title' => 'required|string|max:255',
            'imdbId' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'rated' => 'nullable|string|max:255',
            'runtime' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'actors' => 'nullable|string|max:255',
            'plot' => 'nullable|string',
            'poster' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'imdbRating' => 'nullable|string|max:255',
        ]);
        $userId = $request->user()?->id;
        $movie = $this->dbService->upsertMovie($movieData, $userId);

        if (! $movie) {
            return back()
                ->withErrors(['title' => 'Unable to create movie entry.'])
                ->withInput();
        }

        return redirect()->route('movie', ['id' => $movie->imdbId]);
    }

    public function deleteMovie(Request $request, int $id)
    {
        $userId = $request->user()?->id;
        $this->dbService->deleteMovieByImdbId($id, $userId);

        return response()->json(['message' => 'Movie deleted successfully']);
    }

    public function updateMovie(Request $request, string $id)
    {
        $movieData = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'nullable|string|max:255',
            'rated' => 'nullable|string|max:255',
            'runtime' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'actors' => 'nullable|string|max:255',
            'plot' => 'nullable|string',
            'poster' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'imdbRating' => 'nullable|string|max:255',
        ]);

        $userId = $request->user()?->id;
        $movie = $this->dbService->updateMovieByImdbId($id, $movieData, $userId);

        if (! $movie) {
            return back()
                ->withErrors(['title' => 'Unable to update movie entry.'])
                ->withInput();
        }

        return redirect()->route('movie', ['id' => $movie->imdbId]);
    }

}
