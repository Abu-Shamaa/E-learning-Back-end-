<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelTopic extends Model
{
    use HasFactory;
    protected $table = 'le_topics';
    protected $fillable = [
        'level_id',
        'topic_id',
        'topic_name',


    ];
}
