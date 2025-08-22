<?php

namespace App\Services;

use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserProfileAnswer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function canUpdateProfile(User $user): bool
    {
        return !$user->profileAnswers()
            ->whereDate('updated_at', Carbon::today())
            ->exists();
    }

    public function updateProfile(User $user, array $answers): array
    {
        if (!$this->canUpdateProfile($user)) {
            return [
                'success' => false,
                'message' => 'Profile can only be updated once per day',
            ];
        }

        DB::transaction(function () use ($user, $answers) {
            foreach ($answers as $questionId => $answer) {
                $answerValue = is_array($answer) ? json_encode($answer) : $answer;
                
                UserProfileAnswer::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'question_id' => $questionId,
                    ],
                    [
                        'answer' => $answerValue,
                        'updated_at' => now(),
                    ]
                );
            }

            PointsTransaction::create([
                'user_id' => $user->id,
                'points' => config('business.points.profile_update_points'),
                'reason' => 'Profile update',
                'is_claimed' => false,
            ]);
        });

        return [
            'success' => true,
            'message' => 'Profile updated successfully',
            'points_earned' => config('business.points.profile_update_points'),
        ];
    }
}
