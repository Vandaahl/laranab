<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'tmdb_id'])]
class Crew extends Model
{
    // Laravel by default assumes the table name is plural of the model name, which is not the case here.
    protected $table = 'crew';

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class)
            ->withPivot([
                'job'
            ])
            ->withTimestamps();
    }
}
