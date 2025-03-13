<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quiz;
use App\Models\UserQuizSubmission;
use Illuminate\Support\Facades\Cache;

class CountController extends Controller
{
    public function getCounts()
    {
        $counts = Cache::remember('counts', 3600, function () {
            $userCount = User::count();
            $quizCount = Quiz::count();
            $submissionCount = UserQuizSubmission::count();
            return [
                'user_count' => $userCount,
                'quiz_count' => $quizCount,
                'submission_count' => $submissionCount
            ];
        });

        return response()->json([
            'status' => 'success',
            'counts' => $counts,
        ]);
    }

    public function getUserCount()
    {
        $userCount = Cache::remember('user_counts', 86400 ,function() {
            return User::count();
        });

        return response()->json([
            'status' => 'success',
            'user_count' => $userCount,
        ]);
    }
}