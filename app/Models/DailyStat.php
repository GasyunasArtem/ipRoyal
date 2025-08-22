<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'created_count',
        'claimed_count',
        'usd_claimed',
    ];

    protected $casts = [
        'date' => 'date',
        'usd_claimed' => 'decimal:2',
    ];
}
