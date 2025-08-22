<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointsClaimResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => $this['success'],
            'message' => $this['message'],
            'points_claimed' => $this->when($this['success'], $this['points_claimed'] ?? 0),
            'usd_earned' => $this->when($this['success'], '$' . number_format((float)($this['usd_earned'] ?? 0), 2)),
            'transactions_claimed' => $this->when($this['success'], $this['transactions_claimed'] ?? 0),
        ];
    }
}
