<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    protected $fillable = [
        'email',
        'token',
        'model',
        'rpd',
        'rpd_default',
        'reloaded_at',
        'user_id',
        'department_id',
        'status',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function department(): HasOne
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'account_id', 'id');
    }
}
