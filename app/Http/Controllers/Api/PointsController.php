<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClaimPointsRequest;
use App\Http\Resources\PointsClaimResource;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct(
        private PointsService $pointsService
    ) {}

    public function getTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->get('status', 'unclaimed'); // unclaimed, claimed, all
        
        $query = $user->pointsTransactions()->orderBy('created_at', 'desc');
        
        if ($status === 'unclaimed') {
            $query->where('is_claimed', false);
        } elseif ($status === 'claimed') {
            $query->where('is_claimed', true);
        }
        
        $transactions = $query->paginate(20);
        
        return response()->json([
            'transactions' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
            'summary' => [
                'total_unclaimed' => $user->pointsTransactions()->where('is_claimed', false)->count(),
                'total_unclaimed_points' => $user->pointsTransactions()->where('is_claimed', false)->sum('points'),
                'total_claimed' => $user->pointsTransactions()->where('is_claimed', true)->count(),
                'total_claimed_points' => $user->pointsTransactions()->where('is_claimed', true)->sum('points'),
            ]
        ]);
    }

    public function claimPoints(ClaimPointsRequest $request): JsonResponse
    {
        $transactionIds = $request->validated()['transaction_ids'] ?? null;
        
        $result = $this->pointsService->claimPoints(
            $request->user(),
            $transactionIds
        );

        $status = $result['success'] ? 200 : 422;

        return response()->json(new PointsClaimResource($result), $status);
    }
}
