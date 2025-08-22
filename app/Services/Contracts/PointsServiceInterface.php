<?php

namespace App\Services\Contracts;

use App\Models\User;

interface PointsServiceInterface
{
    public function claimPoints(User $user, ?array $transactionIds = null): array;
}
