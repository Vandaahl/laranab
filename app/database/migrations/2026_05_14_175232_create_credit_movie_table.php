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
        Schema::create('credit_movie', function (Blueprint $table) {
            $table->foreignId('credit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('movie_id')
                ->constrained()
                ->cascadeOnDelete();

            // Actor or director.
            $table->string('job');

            $table->primary(['credit_id', 'movie_id', 'job']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_movie');
    }
};
