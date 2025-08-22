<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'] ?? $this->date,
            'created_count' => (int)($this['created_count'] ?? $this->created_count ?? 0),
            'claimed_count' => (int)($this['claimed_count'] ?? $this->claimed_count ?? 0),
            'usd_claimed' => '$' . number_format((float)($this['usd_claimed'] ?? $this->usd_claimed ?? 0), 2),
        ];
    }
}
