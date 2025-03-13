<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestionAnswer extends Model
{
    use HasFactory;

    protected $fillable=[
        'question_answer_text',
        'correct_answer',
        'question_id',
    ];

    protected $primaryKey = 'question_answer_id';

    public function question() {
        return $this->belongsTo(QuizQuestion::class, 'question_id'); 
    }
}
