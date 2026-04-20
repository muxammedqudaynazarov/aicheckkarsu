<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'id',
        'name',
        'status'
    ];

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'level_id', 'id');
    }
}
