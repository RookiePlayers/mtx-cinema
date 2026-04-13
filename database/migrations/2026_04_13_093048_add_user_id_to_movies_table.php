<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('movies', 'user_id')) {
            return;
        }

        Schema::table('movies', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('imdbRating')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('movies', 'user_id')) {
            return;
        }

        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
