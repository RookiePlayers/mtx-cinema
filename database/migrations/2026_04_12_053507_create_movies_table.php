<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('imdbID')->unique();
            $table->string('year');
            $table->string('rated');
            $table->string('runtime');
            $table->string('genre');
            $table->string('actors');
            $table->text('plot');
            $table->string('poster');
            $table->string('languages');
            $table->string('imdbRating');
            $table->timestamps();
        });

        Schema::create('userSearch', function (Blueprint $table){
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('movie_id')->index();
            $table->string('searchTerm');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userSearch');
        Schema::dropIfExists('movies');
    }
};
