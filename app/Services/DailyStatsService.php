<?php

namespace App\Services;

use App\Models\DailyStat;
use App\Models\PointsTransaction;
use App\Services\PointsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyStatsService
{
    public function calculateStatsForDate(Carbon $date): array
    {
        $dateString = $date->toDateString();

        $stats = PointsTransaction::whereDate('created_at', $dateString)
            ->selectRaw('
                COUNT(*) as created_count,
                SUM(CASE WHEN is_claimed = 1 THEN 1 ELSE 0 END) as claimed_count,
                SUM(CASE WHEN is_claimed = 1 THEN points * ' . config('business.points.point_to_usd_rate') . ' ELSE 0 END) as usd_claimed
            ')
            ->first();

        return [
            'date' => $dateString,
            'created_count' => $stats->created_count ?? 0,
            'claimed_count' => $stats->claimed_count ?? 0,
            'usd_claimed' => round($stats->usd_claimed ?? 0, 2),
        ];
    }

    public function saveDailyStats(Carbon $date): DailyStat
    {
        $stats = $this->calculateStatsForDate($date);

        return DailyStat::updateOrCreate(
            ['date' => $stats['date']],
            [
                'created_count' => $stats['created_count'],
                'claimed_count' => $stats['claimed_count'],
                'usd_claimed' => $stats['usd_claimed'],
            ]
        );
    }

    public function calculateStatsForYesterday(): DailyStat
    {
        return $this->saveDailyStats(Carbon::yesterday());
    }

    public function calculateStatsForDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $results = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $results[] = $this->saveDailyStats($currentDate->copy());
            $currentDate->addDay();
        }

        return $results;
    }

    public function getStatsForPeriod(int $days = null): array
    {
        $days = $days ?? config('business.stats.default_period_days');
        
        return DailyStat::where('date', '>=', Carbon::now()->subDays($days))
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }

    public function getTotalStats(): array
    {
        $totals = DailyStat::selectRaw('
            SUM(created_count) as total_created,
            SUM(claimed_count) as total_claimed,
            SUM(usd_claimed) as total_usd_claimed,
            COUNT(*) as days_tracked
        ')->first();

        return [
            'total_transactions_created' => $totals->total_created ?? 0,
            'total_transactions_claimed' => $totals->total_claimed ?? 0,
            'total_usd_claimed' => round($totals->total_usd_claimed ?? 0, 2),
            'days_tracked' => $totals->days_tracked ?? 0,
            'average_daily_created' => $totals->days_tracked > 0 
                ? round(($totals->total_created ?? 0) / $totals->days_tracked, 2) 
                : 0,
            'average_daily_claimed' => $totals->days_tracked > 0 
                ? round(($totals->total_claimed ?? 0) / $totals->days_tracked, 2) 
                : 0,
        ];
    }
}