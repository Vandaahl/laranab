<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'tmdb_id'])]
class Credit extends Model
{
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class)
            ->withPivot([
                'job'
            ])
            ->withTimestamps();
    }
}
