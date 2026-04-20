<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class File extends Model
{
    protected $fillable = [
        'file_url',
        'uuid',
        'student_id',
        'lesson_id',
        'overall',
        'ticket_number',
        'status',
        'participant'
    ];

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'id', 'student_id');
    }

    public function lesson(): HasOne
    {
        return $this->hasOne(Lesson::class, 'id', 'lesson_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'file_id', 'id');
    }

    public function over_all()
    {
        $res = $this->results;
        $point = 0;
        foreach ($res as $result) {
            $point += $result->point;
        }
        return $point;
    }
}
