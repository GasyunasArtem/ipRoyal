<?php

namespace App\Console\Commands;

use App\Jobs\CalculateDailyStats;
use App\Services\DailyStatsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateDailyStatsCommand extends Command
{
    protected $signature = 'stats:calculate-daily 
                            {--date= : Date to calculate stats for (YYYY-MM-DD format, defaults to yesterday)}
                            {--queue : Queue the job instead of running synchronously}
                            {--range= : Calculate for a range of days back from date}';

    protected $description = 'Calculate daily statistics for points transactions';

    public function handle(DailyStatsService $dailyStatsService): int
    {
        $dateOption = $this->option('date');
        $queueOption = $this->option('queue');
        $rangeOption = $this->option('range');

        try {
            $date = $dateOption ? Carbon::parse($dateOption) : Carbon::yesterday();
        } catch (\Exception $e) {
            $this->error("Invalid date format. Please use YYYY-MM-DD format.");
            return self::FAILURE;
        }

        $this->info("Calculating daily stats for: {$date->toDateString()}");

        if ($rangeOption && is_numeric($rangeOption)) {
            return $this->handleDateRange($dailyStatsService, $date, (int) $rangeOption, $queueOption);
        }

        if ($queueOption) {
            CalculateDailyStats::dispatch($date);
            $this->info("Daily stats calculation job queued for {$date->toDateString()}");
            return self::SUCCESS;
        }

        try {
            $result = $dailyStatsService->saveDailyStats($date);
            
            $this->info("Daily stats calculated successfully:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Date', $result->date],
                    ['Transactions Created', number_format($result->created_count)],
                    ['Transactions Claimed', number_format($result->claimed_count)],
                    ['USD Claimed', '$' . number_format($result->usd_claimed, 2)],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to calculate daily stats: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function handleDateRange(DailyStatsService $dailyStatsService, Carbon $endDate, int $days, bool $queue): int
    {
        $startDate = $endDate->copy()->subDays($days - 1);
        
        $this->info("Calculating stats for date range: {$startDate->toDateString()} to {$endDate->toDateString()}");

        if ($queue) {
            $currentDate = $startDate->copy();
            $jobCount = 0;
            
            while ($currentDate->lte($endDate)) {
                CalculateDailyStats::dispatch($currentDate->copy());
                $jobCount++;
                $currentDate->addDay();
            }
            
            $this->info("Queued {$jobCount} daily stats calculation jobs");
            return self::SUCCESS;
        }

        try {
            $results = $dailyStatsService->calculateStatsForDateRange($startDate, $endDate);
            
            $this->info("Daily stats calculated for {$days} days:");
            
            $tableData = [];
            $totalCreated = 0;
            $totalClaimed = 0;
            $totalUsd = 0;
            
            foreach ($results as $result) {
                $tableData[] = [
                    $result->date,
                    number_format($result->created_count),
                    number_format($result->claimed_count),
                    '$' . number_format($result->usd_claimed, 2),
                ];
                $totalCreated += $result->created_count;
                $totalClaimed += $result->claimed_count;
                $totalUsd += $result->usd_claimed;
            }
            
            $tableData[] = ['---', '---', '---', '---'];
            $tableData[] = [
                'TOTAL',
                number_format($totalCreated),
                number_format($totalClaimed),
                '$' . number_format($totalUsd, 2),
            ];

            $this->table(
                ['Date', 'Created', 'Claimed', 'USD Claimed'],
                $tableData
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to calculate daily stats range: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}