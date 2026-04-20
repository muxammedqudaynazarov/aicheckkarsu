<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Curriculum extends Model
{
    protected $fillable = [
        'name',
        'department_id',
        'status',
    ];

    public function department(): HasOne
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'curriculum_id', 'id');
    }
}
