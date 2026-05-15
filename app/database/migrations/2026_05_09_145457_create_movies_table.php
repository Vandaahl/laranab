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
            $table->string('imdb_id')->unique();
            $table->unsignedBigInteger('tmdb_id')->nullable()->unique();
            $table->string('title')->index();
            $table->string('original_title')->nullable()->index();
            $table->year('year')->index();
            $table->string('poster')->nullable();
            $table->text('overview')->nullable();
            $table->decimal('imdb_score', 3, 2)->nullable();
            $table->smallInteger('runtime')->nullable();
            $table->string('original_language')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
