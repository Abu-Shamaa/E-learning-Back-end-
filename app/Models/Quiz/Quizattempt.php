<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quizattempt extends Model
{
    use HasFactory;
    protected $table = 'quizattempts';
    protected $fillable = [
        'quiz_id',
        'question_id',
        'user_id',
        'attempt_ans',
        'points',



    ];
}
