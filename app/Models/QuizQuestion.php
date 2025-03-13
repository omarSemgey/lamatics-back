<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable=[
        'question_description',
        'question_image',
        'quiz_id',
    ];

    protected $primaryKey = 'quiz_question_id';

    public function quiz() {
        return $this->belongsTo(Quiz::class); 
    }

    public function questionAnswers()
    {
    return $this->hasMany(QuizQuestionAnswer::class, 'question_id');
    }
}
