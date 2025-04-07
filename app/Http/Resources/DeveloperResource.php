<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class DeveloperResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $endDate = Carbon::parse($this->end_date);
        $today = Carbon::today();
        $daysLeft = $today->diffInDays($endDate, false);
        return [
            'id' => $this->id,
            'project_name' => $this->project_name,
            'project_type' => $this->project_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $daysLeft < 0 ? 'expired' : ($daysLeft <= 10 ? 'warning' : 'active'),            'project_leader' => $this->project_leader,
            'support' => $this->support,
            'summary' => $this->summary ? url('storage/' . $this->summary) : null,
            'cost' => $this->cost,
            'profit_margin' => $this->profit_margin,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),


        ];
        }
    }

