<?php

namespace App\Http\Controllers;

use App\Models\UserQuizSubmission;
use App\Http\Requests\StoreUserQuizSubmissionRequest;
use App\Models\QuizQuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class UserQuizSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $requestUserId = $request->input('user_id');

        $validator = Validator::make(
            [ 'user_id' => $requestUserId ],
            [ 'user_id' => 'nullable|integer|exists:users,user_id' ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
            ], 401);
        }

        $validatedSearch = $validator->validated();

        $userId = $validatedSearch['user_id'] ?? $user->user_id;

        $page = request()->get('page', 1);
        $cacheKey = 'user_submissions_' . $userId . '_page_' . $page;
        $minutes = 60;

        $submissions = Cache::tags(['submission_list'])->remember($cacheKey, $minutes * 60, function () use ($userId) {
            return UserQuizSubmission::where('user_id', $userId)
                ->with(['quiz' => function ($query) {
                    $query->withQuestionCount();
                }])
                ->paginate(10);
        });

        return response()->json([
            'submissions' => $submissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserQuizSubmissionRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $answerIds = array_values($validatedData['answers']);

            $correctAnswers = QuizQuestionAnswer::whereIn('question_answer_id', $answerIds)
                ->where('correct_answer', true)
                ->count();

            $totalQuestions = count($validatedData['answers']);
            $scorePercentage = ($correctAnswers / $totalQuestions) * 100;

            UserQuizSubmission::create([
                'user_id' => auth()->id(),
                'quiz_id' => $validatedData['quiz_id'],
                'score' => $scorePercentage,
                'answers' => $validatedData['answers']
            ]);

            DB::commit();

            Cache::forget('counts');

            Cache::tags([
                'submission_list',
                'submission_searches',
            ])->flush();

            return response()->json([
                'status' => 'success',
                'message' => 'Quiz submitted successfully',
            ]);

        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Submission failed: ' . $err->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($quizId, $userId = null)
    {
        $validator = Validator::make(
            [ 'user_id' => $userId ],
            [ 'user_id' => 'nullable|integer|exists:users,user_id' ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedSearch = $validator->validated();

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
            ], 401);
        }

        $userId = $validatedSearch['user_id'] ?? $user->user_id;

        $cacheKey = 'submission_' . $quizId . '_' . $userId;
        $hours = 1;

        $submission = Cache::remember($cacheKey, $hours * 3600, function () use ($userId, $quizId) {
            return UserQuizSubmission::with([
                'quiz' => function($query) {
                    $query->with('quizQuestions.questionAnswers');
                }
            ])->where([
                'user_id' => $userId,
                'quiz_id' => $quizId
            ])->first();
        });

        return response()->json([
            'status' => 'success',
            'submission' => $submission
        ]);
    }

    /**
     * Search for a resource.
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = str_replace(['%', '_'], '', $request->input('search', ''));

            $searchTerm = trim($searchTerm);

            $requestUserId = $request->input('user_id');

            $validator = Validator::make(
                [
                    'search' => $searchTerm,
                    'user_id' => $requestUserId
                ],
                [
                    'search' => 'required|string|max:25',
                    'user_id' => 'nullable|integer|exists:users,user_id'
                ]
            );

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                ], 401);
            }

            $validatedSearch = $validator->validated();

            $userId = $validatedSearch['user_id'] ?? $user->user_id;

            $page = request()->get('page', 1);
            $cacheKey = 'submission_search_' . md5($validatedSearch['search'] . '_page_' . $page);
            $minutes = 15;

            $submissions = Cache::tags(['submission_searches'])->remember($cacheKey, $minutes * 60, function () use ($validatedSearch, $userId) {
                return UserQuizSubmission::with(['quiz' => function ($query) {
                    $query->withQuestionCount();
                }])
                ->join('quizzes', 'user_quiz_submissions.quiz_id', '=', 'quizzes.quiz_id')
                ->where(function ($query) use ($validatedSearch) {
                    $query->where('quizzes.quiz_title', 'LIKE', "%{$validatedSearch['search']}%")
                        ->orWhere('quizzes.quiz_description', 'LIKE', "%{$validatedSearch['search']}%");
                })
                ->where('user_quiz_submissions.user_id', $userId)
                ->orderByRaw('
                    CASE 
                        WHEN quizzes.quiz_title LIKE ? THEN 1
                        WHEN quizzes.quiz_title LIKE ? THEN 2
                        WHEN quizzes.quiz_title LIKE ? THEN 3
                        ELSE 4
                    END ASC',
                    [
                        $validatedSearch['search'],         
                        $validatedSearch['search'] . '%',   
                        '%' . $validatedSearch['search'] . '%' 
                    ]
                )
                ->orderByRaw('
                    CASE 
                        WHEN quizzes.quiz_description LIKE ? THEN 1
                        WHEN quizzes.quiz_description LIKE ? THEN 2
                        WHEN quizzes.quiz_description LIKE ? THEN 3
                        ELSE 4
                    END ASC',
                    [
                        $validatedSearch['search'],       
                        $validatedSearch['search'] . '%',   
                        '%' . $validatedSearch['search'] . '%'
                    ]
                )
                ->select('user_quiz_submissions.*')
                ->paginate(10);
            });

            return response()->json([
                'status' => 'success',
                'submissions' => $submissions
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search failed',
            ], 500);
        }
    }
}