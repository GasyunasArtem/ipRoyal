<?php

namespace App\Jobs;

use App\Services\DailyStatsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateDailyStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private ?Carbon $date = null
    ) {
        $this->date = $this->date ?? Carbon::yesterday();
    }

    public function handle(DailyStatsService $dailyStatsService): void
    {
        try {
            $result = $dailyStatsService->saveDailyStats($this->date);
            
            Log::info('Daily stats calculated successfully', [
                'date' => $this->date->toDateString(),
                'created_count' => $result->created_count,
                'claimed_count' => $result->claimed_count,
                'usd_claimed' => $result->usd_claimed,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to calculate daily stats', [
                'date' => $this->date->toDateString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CalculateDailyStats job failed', [
            'date' => $this->date->toDateString(),
            'error' => $exception->getMessage(),
        ]);
    }
}