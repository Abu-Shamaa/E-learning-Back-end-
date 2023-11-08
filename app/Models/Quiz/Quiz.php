<?php

namespace App\Models\Quiz;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $table = 'quizzes';

    protected $fillable = [
        'quiz_name',
        'course_id',
        'status',
        'test_time',
        'test_end',
        'start_date',



    ];
    protected $casts = [
        'test_end' => 'datetime:Y-m-d\TH:i:s.u\Z',
        'start_date' => 'datetime:Y-m-d\TH:i:s.u\Z',

    ];
    public function qlevel()
    {

        return $this->belongsToMany(Level::class);
    }
    public function ahistory()
    {

        return $this->hasMany(AttemptHistory::class);
    }
}
