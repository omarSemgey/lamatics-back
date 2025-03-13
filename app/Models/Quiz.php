<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable=[
        'quiz_title',
        'quiz_description',
        'quiz_difficulty',
    ];

    protected $primaryKey = 'quiz_id';

    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    public function userSubmissions()
    {
        return $this->hasMany(UserQuizSubmission::class, 'quiz_id');
    }

    public function scopeWithQuestionCount($query)
    {
        return $query->withCount('quizQuestions');
    }
}
