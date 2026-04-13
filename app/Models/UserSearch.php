<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSearch extends Model
{
    use HasFactory;

    protected $table = 'userSearch';

    protected $fillable = [
        'user_id',
        'guest_token',
        'movie_id',
        'searchTerm',
    ];
}
