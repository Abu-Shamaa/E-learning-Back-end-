<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;
    protected $table = 'topics';
    protected $fillable = [
        'course_id',
        'topic_name',


    ];

    public function topicArticle()
    {

        return $this->hasMany(TArticle::class);
    }
    public function topicContent()
    {

        return $this->hasMany(TContent::class);
    }
}
