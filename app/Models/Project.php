<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'date', 'employee', 'owner_name', 'owner_number', 'owner_country',
        'project_name', 'project_type', 'price_offer', 'cost',
        'initial_payment', 'profit_margin', 'hosting', 'technical_support','department_id'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

}
