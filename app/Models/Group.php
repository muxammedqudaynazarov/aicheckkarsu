<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Group extends Model
{
    protected $fillable = [
        'id',
        'name',
        'specialty_id',
        'language_id',
        'curriculum_id',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'group_id', 'id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'group_id', 'id');
    }

    public function specialty(): HasOne
    {
        return $this->hasOne(Specialty::class, 'id', 'specialty_id');
    }

    public function language(): HasOne
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }
}
