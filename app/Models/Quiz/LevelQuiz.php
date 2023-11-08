<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelQuiz extends Model
{
    use HasFactory;
    protected $table = 'level_quiz';
    protected $fillable = [
        'level_id',
        'quiz_id',
        'qcount',


    ];
}
