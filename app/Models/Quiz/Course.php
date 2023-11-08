<?php

namespace App\Models\Quiz;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $table = 'courses';
    protected $fillable = [
        'course_name',
        'description',


    ];
    public function topic()
    {
        return $this->hasMany(Topic::class);
    }
    public function level()
    {
        return $this->hasMany(Level::class);
    }
    public function quiz()
    {
        return $this->hasMany(Quiz::class);
    }
    public function question()
    {
        return $this->hasMany(Question::class);
    }
    public function instructor()
    {

        return $this->belongsToMany(User::class);
    }
}
