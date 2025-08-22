<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Points System Configuration
    |--------------------------------------------------------------------------
    */
    'points' => [
        'point_to_usd_rate' => env('POINTS_USD_RATE', 0.01),
        'profile_update_points' => env('PROFILE_UPDATE_POINTS', 5),
        'max_claim_transactions' => env('MAX_CLAIM_TRANSACTIONS', 100),
        'max_claim_per_minute' => env('MAX_CLAIM_PER_MINUTE', 10),
        'initial_wallet_balance' => env('INITIAL_WALLET_BALANCE', 0.00),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stats Configuration
    |--------------------------------------------------------------------------
    */
    'stats' => [
        'default_period_days' => env('STATS_DEFAULT_PERIOD', 30),
        'max_period_days' => env('STATS_MAX_PERIOD', 365),
        'max_history_years' => env('STATS_MAX_HISTORY_YEARS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'auth_per_minute' => env('RATE_LIMIT_AUTH', 10),
        'api_authenticated_per_minute' => env('RATE_LIMIT_API_AUTH', 200),
        'api_guest_per_minute' => env('RATE_LIMIT_API_GUEST', 50),
        'profile_updates_per_day' => env('RATE_LIMIT_PROFILE', 50),
        'points_claims_per_minute' => env('RATE_LIMIT_POINTS', 30),
        'admin_operations_per_minute' => env('RATE_LIMIT_ADMIN', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Limits
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'max_profile_answers' => env('MAX_PROFILE_ANSWERS', 50),
        'max_answer_length' => env('MAX_ANSWER_LENGTH', 1000),
        'max_name_length' => env('MAX_NAME_LENGTH', 255),
        'min_password_length' => env('MIN_PASSWORD_LENGTH', 8),
        'max_password_length' => env('MAX_PASSWORD_LENGTH', 255),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'enable_vpn_blocking' => env('BLOCK_VPN_USERS', true),
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration_minutes' => env('LOCKOUT_DURATION', 15),
    ],
];
