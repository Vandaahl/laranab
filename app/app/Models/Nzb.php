<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['title', 'movie_id', 'guid', 'group', 'size', 'nzb', 'nfo', 'published_at'])]
class Nzb extends Model
{
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    protected function casts(): array
    {
        return [
            // Instead of returning a string, cast to a datetime object for diffForHumans() compatibility
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include NZBs with the given category.
     */
    #[Scope]
    protected function inCategory(Builder $query, Category $category): Builder
    {
        return $query->whereHas('categories', function (Builder $query) use ($category) {
            $query->whereKey($category->id);
        });
    }
}
