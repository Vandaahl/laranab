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
        Schema::create('nzbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('title')->index();
            $table->string('guid');
            $table->string('group');
            $table->string('size');
            $table->string('nzb');
            $table->string('nfo')->nullable();
            $table->dateTime('published_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nzbs');
    }
};
