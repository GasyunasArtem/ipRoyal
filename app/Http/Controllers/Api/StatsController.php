<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatsRequest;
use App\Http\Resources\DailyStatsResource;
use App\Services\DailyStatsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    public function __construct(
        private DailyStatsService $dailyStatsService
    ) {}

    public function getDailyStats(StatsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $days = $validated['days'];

        $stats = $this->dailyStatsService->getStatsForPeriod($days);

        return response()->json([
            'period_days' => $days,
            'stats' => DailyStatsResource::collection($stats),
        ]);
    }

    public function getTotalStats(): JsonResponse
    {
        $stats = $this->dailyStatsService->getTotalStats();

        return response()->json($stats);
    }

    public function getStatsForDate(Request $request, string $date): JsonResponse
    {
        // Validate date format and constraints
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([
                'error' => 'Invalid date format. Please use YYYY-MM-DD format.',
            ], 422);
        }

        try {
            $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid date. Please provide a valid date.',
            ], 422);
        }

        if ($carbonDate->isFuture()) {
            return response()->json([
                'error' => 'Cannot get stats for future dates.',
            ], 422);
        }

        // Prevent requests for very old dates
        $maxHistoryYears = config('business.stats.max_history_years');
        if ($carbonDate->lt(Carbon::now()->subYears($maxHistoryYears))) {
            return response()->json([
                'error' => "Cannot get stats for dates older than {$maxHistoryYears} years.",
            ], 422);
        }

        $stats = $this->dailyStatsService->calculateStatsForDate($carbonDate);

        return response()->json(new DailyStatsResource($stats));
    }

    public function refreshStatsForDate(Request $request, string $date): JsonResponse
    {
        // Same validation as getStatsForDate
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([
                'error' => 'Invalid date format. Please use YYYY-MM-DD format.',
            ], 422);
        }

        try {
            $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid date. Please provide a valid date.',
            ], 422);
        }

        if ($carbonDate->isFuture()) {
            return response()->json([
                'error' => 'Cannot refresh stats for future dates.',
            ], 422);
        }

        $maxHistoryYears = config('business.stats.max_history_years');
        if ($carbonDate->lt(Carbon::now()->subYears($maxHistoryYears))) {
            return response()->json([
                'error' => "Cannot refresh stats for dates older than {$maxHistoryYears} years.",
            ], 422);
        }

        try {
            $result = $this->dailyStatsService->saveDailyStats($carbonDate);
            
            return response()->json([
                'message' => 'Stats refreshed successfully',
                'stats' => new DailyStatsResource($result),
            ]);
        } catch (\Exception $e) {
            Log::error('Stats refresh failed', [
                'date' => $date,
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);
            
            return response()->json([
                'error' => 'Failed to refresh stats. Please try again later.',
            ], 500);
        }
    }
}