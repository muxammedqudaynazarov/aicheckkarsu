<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'file_id',
        'question_number',
        'question_text',
        'description',
        'point',
        'reason',
    ];
}
