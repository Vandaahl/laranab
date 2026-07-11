<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['external_id', 'name', 'parent_id'])]
class Category extends Model
{
    public function nzbs(): BelongsToMany
    {
        return $this->belongsToMany(Nzb::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany|Category
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
