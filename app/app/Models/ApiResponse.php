<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source', 'payload', 'processed_at', 'failed_at', 'attempts', 'last_successful', 'error'])]
class ApiResponse extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array'
        ];
    }
}
