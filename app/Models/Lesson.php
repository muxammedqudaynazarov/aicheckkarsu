<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    protected $fillable = [
        'name',
        'uuid',
        'group_id',
        'level_id',
        'semester_id',
        'edu_year_id',
        'account_id',
        'exam_date',
        'status',
    ];

    protected $casts = [
        'exam_date' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'lesson_id', 'id');
    }

    public function level(): HasOne
    {
        return $this->hasOne(Level::class, 'id', 'level_id');
    }

    public function semester(): HasOne
    {
        return $this->hasOne(Semester::class, 'id', 'semester_id');
    }

    public function eduYear(): HasOne
    {
        return $this->hasOne(EduYear::class, 'id', 'edu_year_id');
    }
    public function canBeChecked(): bool
    {
        // Faqat tekshirilishi kerak bo'lgan va qatnashgan talabalar fayllarini sanaymiz
        $filesCount = $this->files()
            ->where('participant', '0')
            ->where('status', '0')
            ->count();

        // Agar tekshiriladigan fayl umuman yo'q bo'lsa, yolg'on qaytaramiz
        if ($filesCount === 0) {
            return false;
        }

        // Hech bo'lmaganda bitta 'status = 0' va RPD si fayllar sonidan katta/teng bo'lgan akkauntni qidiramiz
        return \App\Models\Account::where('status', '0')
            ->where('rpd', '>=', $filesCount)
            ->exists();
    }

}
