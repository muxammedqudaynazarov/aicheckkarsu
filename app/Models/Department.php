<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Department extends Model
{
    protected $fillable = [
        'name',
        'structure',
        'parent_id',
        'status',
    ];

    public function specialties(): HasMany
    {
        return $this->hasMany(Specialty::class, 'department_id', 'id');
    }

    public function curricula(): HasMany
    {
        return $this->hasMany(Curriculum::class, 'department_id', 'id');
    }

    public function groups()
    {
        return $this->hasManyThrough(
            Group::class,
            Specialty::class,
            'department_id', // Specialty jadvalidagi foreign key
            'specialty_id',  // Group jadvalidagi foreign key
            'id',            // Department jadvalidagi local key
            'id'             // Specialty jadvalidagi local key
        );
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, Group::class);
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Group::class);
    }
}
