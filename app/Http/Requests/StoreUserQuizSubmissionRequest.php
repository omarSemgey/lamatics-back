<?php

namespace App\Http\Requests;

use App\Models\QuizQuestionAnswer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreUserQuizSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 1;
    }

    public function rules(): array
    {
        return [
            'quiz_id' => [
                'required',
                'integer',
                'exists:quizzes,quiz_id',
                Rule::unique('user_quiz_submissions')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })
            ],
            'answers' => 'required|array',
            'answers.*' => 'required|exists:quiz_question_answers,question_answer_id'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $answers = $this->input('answers', []);

            foreach ($answers as $questionId => $answerId) {

                $exists = QuizQuestionAnswer::where('question_answer_id', $answerId)
                    ->where('question_id', $questionId)
                    ->exists();

                if (!$exists) {
                    $validator->errors()->add("answers.$questionId", "The selected answer is invalid for question $questionId.");
                }
            }
        });
    }
}