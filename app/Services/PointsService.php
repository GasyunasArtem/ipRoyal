<?php

namespace App\Services;

use App\Mail\PointsClaimedMail;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Services\Contracts\PointsServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PointsService implements PointsServiceInterface
{
    public function getPointToUsdRate(): float
    {
        return config('business.points.point_to_usd_rate');
    }

    public function claimPoints(User $user, ?array $transactionIds = null): array
    {
        $query = $user->pointsTransactions()->where('is_claimed', false);
        
        if ($transactionIds) {
            $query->whereIn('id', $transactionIds);
        }
        
        $transactions = $query->get();
        
        if ($transactions->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No unclaimed points found',
            ];
        }

        $totalPoints = $transactions->sum('points');
        $usdAmount = $totalPoints * $this->getPointToUsdRate();

        DB::transaction(function () use ($user, $transactions, $usdAmount) {
            PointsTransaction::whereIn('id', $transactions->pluck('id'))
                ->update([
                    'is_claimed' => true,
                    'claimed_at' => Carbon::now(),
                ]);

            $wallet = $user->wallet;
            $wallet->increment('balance_usd', $usdAmount);
        });

        try {
            Mail::to($user->email)->send(new PointsClaimedMail(
                $user,
                $totalPoints,
                $usdAmount,
                $transactions->count()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to send points claimed email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'success' => true,
            'message' => 'Points claimed successfully',
            'points_claimed' => $totalPoints,
            'usd_earned' => $usdAmount,
            'transactions_claimed' => $transactions->count(),
        ];
    }
}
