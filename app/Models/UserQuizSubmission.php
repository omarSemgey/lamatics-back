<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizSubmission extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'quiz_id',
        'score',
        'answers'
    ];

    protected $primaryKey = 'user_submission_id';

    protected $casts = [
        'answers' => 'array'
    ];

    public function user() {
        return $this->belongsTo(User::class); 
    }

    public function quiz() {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id'); 
    }
}
