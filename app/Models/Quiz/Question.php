<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';
    protected $fillable = [
        'course_id',
        'topic_id',
        'level_id',
        'title',
        'q_content',
        'question_type',
        'answer',


    ];


    public function option()
    {
        return $this->hasMany(Option::class,);
    }
}
