<?php

namespace App\Services;

use App\Models\User;

class WalletService
{
    public function getWalletInfo(User $user): array
    {
        $wallet = $user->wallet;
        $unclaimedCount = $user->pointsTransactions()
            ->where('is_claimed', false)
            ->count();

        return [
            'balance_usd' => $wallet->balance_usd,
            'unclaimed_count' => $unclaimedCount,
        ];
    }
}
