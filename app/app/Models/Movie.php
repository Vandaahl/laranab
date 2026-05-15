<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['imdb_id', 'tmdb_id', 'title', 'original_title', 'year', 'poster', 'overview', 'imdb_score', 'runtime', 'original_language'])]
class Movie extends Model
{
    public function nzbs(): HasMany
    {
        return $this->hasMany(Nzb::class);
    }

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class)
            ->withPivot([
                'job'
            ])
            ->withTimestamps();
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class)
            ->wherePivot('job', 'actor');
    }

    public function directors(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class)
            ->wherePivot('job', 'director');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}
