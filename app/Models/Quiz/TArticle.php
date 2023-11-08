<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TArticle extends Model
{
    use HasFactory;
    protected $table = 'topic_articles';
    protected $fillable = [
        'topic_id',
        'name',
        'slug',
        'content',

    ];
}
