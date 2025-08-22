<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ProxyCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private ProxyCheckService $proxyCheckService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $userIp = $request->ip();
        
        // Временное логирование для отладки
        Log::info('Registration Debug', [
            'user_ip' => $userIp,
            'remote_addr' => $request->server('REMOTE_ADDR'),
            'x_forwarded_for' => $request->server('HTTP_X_FORWARDED_FOR'),
            'email' => $request->email
        ]);
        
        if (config('services.proxycheck.enabled') && config('services.proxycheck.block_vpn')) {
            if ($this->proxyCheckService->isVpnOrProxy($userIp)) {
                return response()->json([
                    'message' => 'VPN/Proxy connections are not allowed',
                    'error' => 'vpn_detected',
                ], 403);
            }
        }

        $country = $request->country ?? $this->proxyCheckService->getCountryByIp($userIp);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $country,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance_usd' => config('business.points.initial_wallet_balance', 0.00),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
