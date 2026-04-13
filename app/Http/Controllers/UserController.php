<?php

namespace App\Http\Controllers;

use App\Services\DBService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
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

        return Inertia::render('User', [
            'savedMovies' => $this->dbService->getSavedMovies($userId, $scope),
            'scope' => $scope,
        ]);
    }
    public function createMovie(Request $request)
    {
        $movieData = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'genre' => 'nullable|string|max:255',
            'director' => 'nullable|string|max:255',
            'actors' => 'nullable|string|max:255',
            'plot' => 'nullable|string',
        ]);
        $userId = $request->user()?->id;
        $movie = $this->dbService->upsertMovie($movieData, $userId);

        return response()->json($movie, 201);
    }

    public function deleteMovie(Request $request, int $id)
    {
        $userId = $request->user()?->id;
        $this->dbService->deleteMovieByImdbId($id, $userId);

        return response()->json(['message' => 'Movie deleted successfully']);
    }

}
