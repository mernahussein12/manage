<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'month' => $this->month,
            'year' => $this->year,
            'total_revenue' => $this->total_revenue,
            'total_expenses' => $this->total_expenses,
            'net_profit' => $this->net_profit,
        ];
    }
}
