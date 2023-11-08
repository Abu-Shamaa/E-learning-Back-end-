<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;
    protected $table = 'levels';
    protected $fillable = [

        'level_name',
        'course_id',


    ];

    public function lquiz()
    {

        return $this->belongsToMany(Quiz::class);
    }

    public function ltopic()
    {

        return $this->hasMany(LevelTopic::class);
    }
    public function lquestion()
    {

        return $this->hasMany(Question::class);
    }
}
