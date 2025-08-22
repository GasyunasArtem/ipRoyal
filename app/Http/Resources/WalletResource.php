<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'balance_usd' => number_format((float)$this['balance_usd'], 2, '.', ''),
            'unclaimed_count' => $this['unclaimed_count'],
            'formatted_balance' => '$' . number_format((float)$this['balance_usd'], 2),
        ];
    }
}
