<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PaginationInput;
use App\Services\DBService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function __construct(
        private readonly DBService $dbService,
    ) {
    }


    public function index(Request $request)
    {
         return Inertia::render('Search', [
            'query' => (string) ($request->input('query') ?? ''),
        ]);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid search parameters.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();
            $query = (string) ($validated['query'] ?? '');
            $userId = $request->user()?->id;
            $existingGuestToken = $request->cookie('guest_token');
            $guestToken = $userId === null
                ? ($existingGuestToken ?? Str::uuid()->toString())
                : null;
            $pagination = new PaginationInput(
                null,
                (int) ($validated['limit'] ?? 10),
                (int) ($validated['offset'] ?? 0),
            );

            $paginationData = $this->dbService->searchMovie($query, $userId, $guestToken, $pagination);

            $response = response()->json($paginationData);

            if ($guestToken !== null && $existingGuestToken === null) {
                $response->headers->setCookie(cookie('guest_token', $guestToken, 60 * 24 * 30));
            }

            return $response;
        } catch (Throwable $e) {
            Log::error('Movie search failed.', [
                'query' => $request->input('query'),
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Unable to search movies right now.',
            ], 500);
        }
    }
}
