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
        Schema::create('movie_dump', function (Blueprint $table) {
            $table->id();
            $table->string('imdbId')->unique();
            $table->foreignId('movie_id')->nullable()->constrained('movies')->nullOnDelete();
            $table->integer('total_movies')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_dump');
    }
};
