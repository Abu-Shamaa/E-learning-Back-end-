<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptHistory extends Model
{
    use HasFactory;
    protected $table = 'attempt_histories';

    protected $fillable = [
        'quiz_id',
        'user_id',
        'is_attempt',
        'total_question',
        'total_point',




    ];
}
