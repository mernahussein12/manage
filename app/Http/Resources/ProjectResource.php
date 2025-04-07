<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'employee' => $this->employee,
            'owner_name' => $this->owner_name,
            'owner_number' => $this->owner_number,
            'owner_country' => $this->owner_country,
            'project_name' => $this->project_name,
            'project_type' => $this->project_type,
            'price_offer' => $this->price_offer ? url($this->price_offer) : null,
            'cost' => $this->cost,
            'initial_payment' => $this->initial_payment,
            'profit_margin' => $this->profit_margin,
            'hosting' => $this->hosting,
            'technical_support' => $this->technical_support,
            'status'    => $this->status ?? 'pending', // تأكد من تضمين الحالة الافتراضية

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

