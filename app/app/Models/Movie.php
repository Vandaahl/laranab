<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

#[Fillable(['imdb_id', 'tmdb_id', 'title', 'original_title', 'year', 'poster', 'overview', 'imdb_score', 'tmdb_score', 'runtime', 'original_language'])]
class Movie extends Model
{
    public function nzbs(): HasMany
    {
        return $this->hasMany(Nzb::class)->orderByDesc('published_at');
    }

    public function credits(): BelongsToMany
    {
        return $this->belongsToMany(Credit::class)
            ->withPivot([
                'job'
            ]);
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Credit::class)
            ->wherePivot('job', 'actor');
    }

    public function directors(): BelongsToMany
    {
        return $this->belongsToMany(Credit::class)
            ->wherePivot('job', 'director');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class);
    }

    /**
     * Scope a query to only include movies with the given director.
     */
    #[Scope]
    protected function filterByDirector(Builder $query, ?string $director = null): Builder
    {
        $director = trim((string) $director);
        if ($director === '') return $query;

        return $query->whereHas('directors', function (Builder $q) use ($director) {
            $q->where('credits.name', 'LIKE', '%' . $director . '%');
        });
    }

    /**
     * Scope a query to only include movies with the given actor.
     */
    #[Scope]
    protected function filterByActor(Builder $query, ?string $actor = null): Builder
    {
        $actor = trim((string) $actor);
        if ($actor === '') return $query;

        return $query->whereHas('actors', function (Builder $q) use ($actor) {
            $q->where('credits.name', 'LIKE', '%' . $actor . '%');
        });
    }

    /**
     * Scope a query to only include movies from a certain year (range).
     */
    #[Scope]
    protected function filterByYear(Builder $query, ?int $startYear = null, ?int $endYear = null): Builder
    {
        if ($startYear === null && $endYear === null) return $query;

        if ($startYear === null) $startYear = 1900;
        if ($endYear === null) $endYear = date('Y');

        return $query->whereBetween('year', [$startYear, $endYear]);
    }

    /**
     * Scope a query to only include movies from a certain decade.
     */
    #[Scope]
    protected function filterByDecade(Builder $query, ?int $decade = null): Builder
    {
        if ($decade === null) return $query;

        return $query->whereBetween('year', [$decade, $decade + 9]);
    }
}
