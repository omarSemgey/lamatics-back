<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionAnswer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\UserQuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $cacheKey = 'quizzes_page_' . $page;
        $minutes = 60; 

        $quiz = Cache::tags(['quizzes_list'])->remember($cacheKey, $minutes * 60, function () {
            return Quiz::withQuestionCount()->paginate(10);
        });

        return response()->json([
            'status' => 'success',
            'quizzes' => $quiz,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuizRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $quiz = Quiz::create([
                'quiz_title' => $validatedData['quiz_title'],
                'quiz_description' => $validatedData['quiz_description'],
                'quiz_difficulty' => $validatedData['quiz_difficulty'],
            ]);    

            foreach ($validatedData['questions'] as $index => $questionData) {
                $questionPayload = [
                    'question_description' => $questionData['question_description'],
                    'quiz_id' => $quiz->quiz_id,
                ];

                if (isset($questionData['question_image']) && $questionData['question_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $path = $questionData['question_image']->store('images', 'public');
                    $questionPayload['question_image'] = asset(Storage::url($path));
                }

                $question = $quiz->quizQuestions()->create($questionPayload);

                $answers = [];
                foreach ($questionData['answers'] as $answerData) {
                    $answers[] = new QuizQuestionAnswer([
                        'question_answer_text' => $answerData['question_answer_text'],
                        'correct_answer' => $answerData['correct_answer'],
                        'question_id' => $question->quiz_question_id,
                    ]);
                }

                $question->questionAnswers()->saveMany($answers);
            }    

            DB::commit();

            Cache::forget('counts');
            Cache::tags(['quizzes_list', 'quiz_searches'])->flush();

            return response()->json([
                'message' => 'Quiz created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Quiz creation failed',
                'message' => $e,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cacheKey = 'quiz_' . $id;
        $hours = 1;

        $quiz = Cache::remember($cacheKey, $hours * 3600, function () use ($id) {
            return Quiz::with('quizQuestions.questionAnswers')
            ->findOrFail($id);;
        });

        if($quiz == null){
            return response()->json([
                'status' => 'error',
                'message' => 'quiz doesnt exist',
            ]);
        }

        $user = auth()->user();

        if($user == null){
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        $completed = false;

        $status = null;
        if($user->role == 1){
            $status = UserQuizSubmission::where('user_id', $user->user_id)
            ->where('quiz_id',$id)
            ->first();
        }

        if($status){
            $completed = true;
        }

        return response()->json([
            'status' => 'success',
            'quiz' => $quiz,
            'completed'=> $completed,
        ]);
    }

    /**
     * Search for a quiz
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = str_replace(['%', '_'], '', $request->input('search', ''));

            $searchTerm = trim($searchTerm);

            $validator = Validator::make(
                ['search' => $searchTerm,],
                ['search' => 'required|string|max:25',]
            );

            $validatedSearch = $validator->validated();

            $searchTerm = $validatedSearch['search'];
            $page = request()->get('page', 1);
            $cacheKey = 'quiz_search_' . md5($searchTerm . '_page_' . $page);
            $minutes = 15;

            $quizzes = Cache::tags(['quiz_searches'])->remember($cacheKey, $minutes * 60, function () use ($searchTerm) {
                return Quiz::withQuestionCount()
                ->where(function ($query) use ($searchTerm) {
                    $query->where('quiz_title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('quiz_description', 'LIKE', "%{$searchTerm}%");
                })
                ->orderByRaw('
                    CASE 
                        WHEN quiz_title LIKE ? THEN 1
                        WHEN quiz_title LIKE ? THEN 2
                        WHEN quiz_title LIKE ? THEN 3
                        ELSE 4
                    END ASC
                ', [$searchTerm, $searchTerm.'%', '%'.$searchTerm.'%'])
                ->orderByRaw('
                    CASE 
                        WHEN quiz_description LIKE ? THEN 1
                        WHEN quiz_description LIKE ? THEN 2
                        WHEN quiz_description LIKE ? THEN 3
                        ELSE 4
                    END ASC
                ', [$searchTerm, $searchTerm.'%', '%'.$searchTerm.'%']
                )
                ->paginate(10);
            });

            return response()->json([
            'status' => 'success',
            'quizzes' => $quizzes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search failed'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuizRequest $request, $quizId)
    {
        try {
            DB::beginTransaction();

            $validatedData  = $request->validated();

            $quiz = Quiz::with(['quizQuestions.questionAnswers'])
            ->findOrFail($quizId);

            $quizUpdate = [];

            foreach (['quiz_title', 'quiz_description', 'quiz_difficulty'] as $field) {
                if (array_key_exists($field, $validatedData)) {
                    $quizUpdate[$field] = $validatedData[$field];
                }
            }

            if (!empty($quizUpdate)) {
                $quiz->update($quizUpdate);
            }

            $existingQuestionIds = $quiz->quizQuestions->pluck('quiz_question_id')->toArray();
            $currentQuestionIds = [];

            foreach ($request->questions as $questionIndex => $questionData) {

                $question = $quiz->quizQuestions()->updateOrCreate(
                    ['quiz_question_id' => $questionData['quiz_question_id'] ?? null],
                    ['question_description' => $questionData['question_description']]
                );

                $currentQuestionIds[] = $question->quiz_question_id;

                if (isset($questionData['question_image']) && $questionData['question_image'] instanceof \Illuminate\Http\UploadedFile) {
                    if ($question->question_image) {
                        $parsedUrl = parse_url($question->question_image);
                        $oldImagePath = substr($parsedUrl['path'], strlen('/storage/'));
                        Storage::disk('public')->delete($oldImagePath);
                    }

                    
                    $file = $questionData['question_image'];
                    $path = $file->store('images', 'public');
                    $question->update(['question_image' => Storage::url($path)]);
                }

                foreach ($questionData['answers'] as $answerData) {
                    $answer = $question->questionAnswers()->updateOrCreate(
                        ['question_answer_id' => $answerData['question_answer_id'] ?? null],
                        [
                            'question_answer_text' => $answerData['question_answer_text'],
                            'correct_answer' => $answerData['correct_answer'],
                        ]
                        );
                }
            }

            QuizQuestion::whereIn('quiz_question_id', 
            array_diff($existingQuestionIds, $currentQuestionIds)
            )->delete();

            DB::commit();

            Cache::forget('quiz_' . $quiz->quiz_id);
            Cache::tags(['quizzes_list', 'quiz_searches'])->flush();

            return response()->json([
                'message' => 'Quiz updated successfully',
                'quiz' => $quiz,
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Quiz update failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
    */
    public function destroy($id)
    {
        $quiz = Quiz::find($id);

        if($quiz == null){
            return response()->json([
                'status' => 'error',
                'message' => 'quiz doesnt exist',
            ]);
        }

        try{
            DB::beginTransaction();

            $quiz->delete();

            DB::commit();

            Cache::forget('counts');
            Cache::forget('quiz_' . $quiz->quiz_id);
            Cache::tags(['quizzes_list', 'quiz_searches'])->flush();

            return response()->json([
                'status' => 'success',
                'message' => 'quiz deleted successfully'
            ]);
        }catch(\Throwable $err){
            DB::rollBack();
            return response()->json([
                'status' => $err,
            ]);
        };
    }
}
