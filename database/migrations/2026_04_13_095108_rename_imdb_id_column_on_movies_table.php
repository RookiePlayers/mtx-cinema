<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('movies', 'imdbID') && ! Schema::hasColumn('movies', 'imdbId')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->renameColumn('imdbID', 'imdbId');
            });
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS populate_movie_from_api');
        DB::unprepared('
            CREATE PROCEDURE populate_movie_from_api(
                IN p_title VARCHAR(255),
                IN p_imdb_id VARCHAR(255),
                IN p_year VARCHAR(255),
                IN p_rated VARCHAR(255),
                IN p_runtime VARCHAR(255),
                IN p_genre VARCHAR(255),
                IN p_actors VARCHAR(255),
                IN p_plot TEXT,
                IN p_poster VARCHAR(255),
                IN p_languages VARCHAR(255),
                IN p_imdb_rating VARCHAR(255),
                IN p_user_id BIGINT UNSIGNED
            )
            BEGIN
                DECLARE v_movie_id BIGINT UNSIGNED;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                INSERT INTO movies (
                    title, imdbId, year, rated, runtime, genre,
                    actors, plot, poster, languages, imdbRating, user_id,
                    created_at, updated_at
                )
                VALUES (
                    p_title, p_imdb_id, p_year, p_rated, p_runtime, p_genre,
                    p_actors, p_plot, p_poster, p_languages, p_imdb_rating, p_user_id,
                    NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    year = VALUES(year),
                    rated = VALUES(rated),
                    runtime = VALUES(runtime),
                    genre = VALUES(genre),
                    actors = VALUES(actors),
                    plot = VALUES(plot),
                    poster = VALUES(poster),
                    languages = VALUES(languages),
                    imdbRating = VALUES(imdbRating),
                    user_id = VALUES(user_id),
                    updated_at = NOW();

                SELECT id INTO v_movie_id
                FROM movies
                WHERE imdbId COLLATE utf8mb4_unicode_ci = CONVERT(p_imdb_id USING utf8mb4) COLLATE utf8mb4_unicode_ci
                LIMIT 1;

                INSERT INTO movie_dump (imdbId, movie_id, total_movies, created_at, updated_at)
                VALUES (CONVERT(p_imdb_id USING utf8mb4) COLLATE utf8mb4_unicode_ci, v_movie_id, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    movie_id = VALUES(movie_id),
                    total_movies = VALUES(total_movies),
                    updated_at = NOW();

                COMMIT;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS populate_movie_from_api');

        if (Schema::hasColumn('movies', 'imdbId') && ! Schema::hasColumn('movies', 'imdbID')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->renameColumn('imdbId', 'imdbID');
            });
        }

        DB::unprepared('
            CREATE PROCEDURE populate_movie_from_api(
                IN p_title VARCHAR(255),
                IN p_imdb_id VARCHAR(255),
                IN p_year VARCHAR(255),
                IN p_rated VARCHAR(255),
                IN p_runtime VARCHAR(255),
                IN p_genre VARCHAR(255),
                IN p_actors VARCHAR(255),
                IN p_plot TEXT,
                IN p_poster VARCHAR(255),
                IN p_languages VARCHAR(255),
                IN p_imdb_rating VARCHAR(255),
                IN p_user_id BIGINT UNSIGNED
            )
            BEGIN
                DECLARE v_movie_id BIGINT UNSIGNED;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                INSERT INTO movies (
                    title, imdbID, year, rated, runtime, genre,
                    actors, plot, poster, languages, imdbRating, user_id,
                    created_at, updated_at
                )
                VALUES (
                    p_title, p_imdb_id, p_year, p_rated, p_runtime, p_genre,
                    p_actors, p_plot, p_poster, p_languages, p_imdb_rating, p_user_id,
                    NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    year = VALUES(year),
                    rated = VALUES(rated),
                    runtime = VALUES(runtime),
                    genre = VALUES(genre),
                    actors = VALUES(actors),
                    plot = VALUES(plot),
                    poster = VALUES(poster),
                    languages = VALUES(languages),
                    imdbRating = VALUES(imdbRating),
                    user_id = VALUES(user_id),
                    updated_at = NOW();

                SELECT id INTO v_movie_id
                FROM movies
                WHERE imdbID COLLATE utf8mb4_unicode_ci = CONVERT(p_imdb_id USING utf8mb4) COLLATE utf8mb4_unicode_ci
                LIMIT 1;

                INSERT INTO movie_dump (imdbId, movie_id, total_movies, created_at, updated_at)
                VALUES (CONVERT(p_imdb_id USING utf8mb4) COLLATE utf8mb4_unicode_ci, v_movie_id, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    movie_id = VALUES(movie_id),
                    total_movies = VALUES(total_movies),
                    updated_at = NOW();

                COMMIT;
            END
        ');
    }
};
