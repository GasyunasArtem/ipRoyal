<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function getWallet(Request $request): JsonResponse
    {
        $walletInfo = $this->walletService->getWalletInfo($request->user());

        return response()->json(new WalletResource($walletInfo));
    }
}
