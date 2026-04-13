<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // -- For searching we have 2 options
        // -- 1. Full-Text Search (FTS) - MySQL has built-in support for full-text search on InnoDB tables. This allows you to create a full-text index on one or more columns and perform natural language searches. However, FTS can be complex to set up and may not always provide the best performance for autocomplete scenarios, especially if you need to search across multiple fields (like title, genre, actors) simultaneously.
        // -- 2. Custom Search Index - We can create a separate table to store search terms extracted

        // -- I have decided to go with 2. because it gives us more
        // -- control and can be optimized for our specific use case of autocomplete.

        // -- For faster autocomplete we will be using this table
        // -- and index on the term column for quick lookups.
        Schema::create('movie_search_index', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->cascadeOnDelete();
            $table->string('term')->index();
            $table->string('field_name');
            $table->unique(['movie_id', 'term']);
            $table->timestamps();
        });
        // -- We are going to build a procedure
        // -- that will create our autocompletion search index
        DB::unprepared('DROP PROCEDURE IF EXISTS `rebuild_movie_search_index`');
        DB::unprepared('
            CREATE PROCEDURE rebuild_movie_search_index(
                IN p_movie_id BIGINT UNSIGNED,
                IN p_title TEXT,
                IN p_genre TEXT,
                IN p_actors TEXT
            )
            BEGIN
                DECLARE v_text TEXT;
                DECLARE v_word VARCHAR(255);
                DECLARE v_pos INT;

                DELETE FROM movie_search_index
                WHERE movie_id = p_movie_id;

                SET v_text = LOWER(REPLACE(REPLACE(REPLACE(IFNULL(p_title, ""), ",", " "), ".", " "), "-", " "));
                WHILE LENGTH(TRIM(v_text)) > 0 DO
                    SET v_pos = LOCATE(" ", TRIM(v_text));
                    IF v_pos = 0 THEN
                        SET v_word = TRIM(v_text);
                        SET v_text = "";
                    ELSE
                        SET v_word = LEFT(TRIM(v_text), v_pos - 1);
                        SET v_text = SUBSTRING(TRIM(v_text), v_pos + 1);
                    END IF;

                    IF LENGTH(v_word) >= 3 AND v_word NOT IN ("the", "and", "for", "of") THEN
                        INSERT IGNORE INTO movie_search_index (movie_id, term, field_name)
                        VALUES (p_movie_id, v_word, "title");
                    END IF;
                END WHILE;

                SET v_text = LOWER(REPLACE(REPLACE(REPLACE(IFNULL(p_genre, ""), ",", " "), ".", " "), "-", " "));
                WHILE LENGTH(TRIM(v_text)) > 0 DO
                    SET v_pos = LOCATE(" ", TRIM(v_text));
                    IF v_pos = 0 THEN
                        SET v_word = TRIM(v_text);
                        SET v_text = "";
                    ELSE
                        SET v_word = LEFT(TRIM(v_text), v_pos - 1);
                        SET v_text = SUBSTRING(TRIM(v_text), v_pos + 1);
                    END IF;

                    IF LENGTH(v_word) >= 3 AND v_word NOT IN ("the", "and", "for", "of") THEN
                        INSERT IGNORE INTO movie_search_index (movie_id, term, field_name)
                        VALUES (p_movie_id, v_word, "genre");
                    END IF;
                END WHILE;

                SET v_text = LOWER(REPLACE(REPLACE(REPLACE(IFNULL(p_actors, ""), ",", " "), ".", " "), "-", " "));
                WHILE LENGTH(TRIM(v_text)) > 0 DO
                    SET v_pos = LOCATE(" ", TRIM(v_text));
                    IF v_pos = 0 THEN
                        SET v_word = TRIM(v_text);
                        SET v_text = "";
                    ELSE
                        SET v_word = LEFT(TRIM(v_text), v_pos - 1);
                        SET v_text = SUBSTRING(TRIM(v_text), v_pos + 1);
                    END IF;

                    IF LENGTH(v_word) >= 3 AND v_word NOT IN ("the", "and", "for", "of") THEN
                        INSERT IGNORE INTO movie_search_index (movie_id, term, field_name)
                        VALUES (p_movie_id, v_word, "actors");
                    END IF;
                END WHILE;
            END
        ');

            //    -- We will also create a trigger to automatically update the search index
            // -- whenever a movie is inserted or updated.

        DB::unprepared('DROP TRIGGER IF EXISTS `trg_movies_after_insert`');
        DB::unprepared('
            CREATE TRIGGER trg_movies_after_insert
            AFTER INSERT ON movies
            FOR EACH ROW
            BEGIN
                CALL rebuild_movie_search_index(
                    NEW.id,
                    NEW.title,
                    NEW.genre,
                    NEW.actors
                );
            END
        ');

        DB::unprepared('DROP TRIGGER IF EXISTS `trg_movies_after_update`');
        DB::unprepared('
            CREATE TRIGGER trg_movies_after_update
            AFTER UPDATE ON movies
            FOR EACH ROW
            BEGIN
                CALL rebuild_movie_search_index(
                    NEW.id,
                    NEW.title,
                    NEW.genre,
                    NEW.actors
                );
            END
        ');

        DB::unprepared('DROP TRIGGER IF EXISTS `trg_movies_after_delete`');
        DB::unprepared('
            CREATE TRIGGER trg_movies_after_delete
            AFTER DELETE ON movies
            FOR EACH ROW
            BEGIN
                DELETE FROM movie_search_index WHERE movie_id = OLD.id;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_movies_after_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_movies_after_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_movies_after_delete`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `rebuild_movie_search_index`');
        Schema::dropIfExists('movie_search_index');
    }
};
