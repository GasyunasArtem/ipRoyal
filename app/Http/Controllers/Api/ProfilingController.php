<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\ProfileUpdateResource;
use App\Http\Resources\ProfilingQuestionResource;
use App\Http\Resources\UserResource;
use App\Models\ProfilingQuestion;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfilingController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function getQuestions(): JsonResponse
    {
        $questions = ProfilingQuestion::where('is_active', true)
            ->orderBy('id')
            ->get();

        return response()->json([
            'questions' => ProfilingQuestionResource::collection($questions),
        ]);
    }

    public function getProfile(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info('Profile endpoint reached', [
            'user_id' => $request->user()?->id,
            'headers' => $request->headers->all()
        ]);
        
        $user = $request->user();
        $answers = $user->profileAnswers()
            ->with('question:id,code,text,type')
            ->get()
            ->keyBy('question.code')
            ->map(function ($answer) {
                // Parse JSON answers for multiselect questions
                $answerValue = $answer->answer;
                if ($answer->question->type === 'multiple') {
                    $decoded = json_decode($answer->answer, true);
                    $answerValue = $decoded !== null ? $decoded : $answer->answer;
                }
                
                return [
                    'question_id' => $answer->question_id,
                    'question_text' => $answer->question->text,
                    'question_type' => $answer->question->type,
                    'answer' => $answerValue,
                    'updated_at' => $answer->updated_at->toISOString()
                ];
            });

        return response()->json([
            'user' => new UserResource($user),
            'profile_answers' => $answers,
            'can_update_today' => $this->profileService->canUpdateProfile($user)
        ]);
    }

    public function updateProfile(ProfileUpdateRequest $request): JsonResponse
    {
        $result = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()['answers']
        );

        $status = $result['success'] ? 200 : 422;

        return response()->json(new ProfileUpdateResource($result), $status);
    }
}
