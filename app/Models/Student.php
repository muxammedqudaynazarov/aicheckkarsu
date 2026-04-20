<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'id',
        'name',
        'student_id_number',
        'group_id',
        'level_id',
        'semester_id',
        'edu_year_id',
    ];

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'student_id', 'id');
    }

    public function fileForLesson($lessonId)
    {
        return $this->hasOne(File::class, 'student_id', 'id')->where('lesson_id', $lessonId);
    }
}
