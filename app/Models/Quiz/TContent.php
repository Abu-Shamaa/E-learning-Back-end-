<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TContent extends Model
{
    use HasFactory;
    protected $table = 'topic_contents';
    protected $fillable = [
        'topic_id',
        'file',



    ];
}
