<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'imdbId',
        'year',
        'rated',
        'runtime',
        'genre',
        'actors',
        'plot',
        'poster',
        'languages',
        'imdbRating',
        'user_id',
    ];

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_saved_movies')
            ->withTimestamps();
    }

    public function scopeSearchFast(Builder $query, string $term): Builder
    {
        $normalizedTerm = strtolower(trim($term));

        if ($normalizedTerm === '') {
            return $query->select('movies.*');
        }

        return $query
            ->select('movies.*')
            ->selectRaw(
                'MAX(CASE WHEN msi.term = ? THEN 3 ELSE 0 END) as exact_score',
                [$normalizedTerm]
            )
            ->selectRaw(
                "MAX(CASE
                    WHEN msi.field_name = 'title' THEN 2
                    WHEN msi.field_name = 'genre' THEN 1
                    ELSE 0
                END) as field_score"
            )
            ->join('movie_search_index as msi', 'msi.movie_id', '=', 'movies.id')
            ->where('msi.term', 'like', $normalizedTerm.'%')
            ->groupBy('movies.id')
            ->orderByDesc('exact_score')
            ->orderByDesc('field_score')
            ->orderBy('movies.title');
    }

}
