<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EduYear extends Model
{
    protected $fillable = [
        'id',
        'name',
        'status'
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
