<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreQuizRequest extends FormRequest
{
    

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role === 2;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'quiz_title' => 'required|string|min:3|max:50|unique:quizzes',
            'quiz_description' => 'required|string|min:3|max:100',
            'quiz_difficulty' => 'required|integer|between:1,3',

            'questions' => 'required|array|min:1|max:50',
            'questions.*.question_description' => 'required|string|min:3|max:100',
            'questions.*.question_image' =>  [
                'sometimes',
                'image',
                'mimes:png,jpg,jpeg',
                'max:4096',
                Rule::dimensions()->maxWidth(2000)->maxHeight(2000),
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/\.[^.]+\./', $value->getClientOriginalName())) {
                        $fail('Invalid image file name detected.');
                    }
                }
            ],

            'questions.*.answers' => 'required|array|size:4',
            'questions.*.answers.*.question_answer_text' => 'required|string|min:1|max:50',
            'questions.*.answers.*.correct_answer' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            // Quiz-level errors
            'quiz_title.required' => 'Quiz title is required',
            'quiz_title.string' => 'Quiz title must be text',
            'quiz_title.min' => 'Quiz title must be at least 3 characters',
            'quiz_title.max' => 'Quiz title cannot exceed 50 characters',

            'quiz_description.required' => 'Quiz description is required',
            'quiz_description.string' => 'Quiz description must be text',
            'quiz_description.min' => 'Quiz description must be at least 3 characters',
            'quiz_description.max' => 'Quiz description cannot exceed 100 characters',

            'quiz_difficulty.required' => 'Please select a difficulty level',
            'quiz_difficulty.integer' => 'Invalid difficulty selection',
            'quiz_difficulty.between' => 'Difficulty must be between 1 and 3',

            // Questions array errors
            'questions.required' => 'At least one question is required',
            'questions.array' => 'Invalid questions format',
            'questions.min' => 'At least one question is required',
            'questions.max' => 'Maximum of 40 questions allowed',

            // Question-level errors
            'questions.*.question_description.required' => 'Question description is required',
            'questions.*.question_description.string' => 'Question description must be text',
            'questions.*.question_description.min' => 'Question description must be at least 3 characters',
            'questions.*.question_description.max' => 'Question description cannot exceed 100 characters',

            'questions.*.question_image.image' => 'Must upload a valid image',
            'questions.*.question_image.mimes' => 'Allowed image formats: PNG, JPG, JPEG',
            'questions.*.question_image.max' => 'Image size must be less than 4MB',
            'questions.*.question_image.dimensions' => 'Image dimensions cannot exceed 2000x2000 pixels',
            'questions.*.question_image' => 'Invalid image file name format',

            // Answers array errors
            'questions.*.answers.required' => 'Each question must have 4 answers',
            'questions.*.answers.array' => 'Invalid answers format',
            'questions.*.answers.size' => 'Each question must have exactly 4 answers',

            // Answer-level errors
            'questions.*.answers.*.question_answer_text.required' => 'Answer text is required',
            'questions.*.answers.*.question_answer_text.string' => 'Answer must be text',
            'questions.*.answers.*.question_answer_text.max' => 'Answer cannot exceed 50 characters',
            
            'questions.*.answers.*.correct_answer.required' => 'Please select one correct answer',
            'questions.*.answers.*.correct_answer.boolean' => 'Invalid correct answer selection',

            // Custom validator message for exactly one correct answer
            'questions.*.answers' => 'Each question must have exactly one correct answer'
        ];
    }
}
