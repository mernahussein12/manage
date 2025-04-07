<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamLeadRequest extends Model
{
    use HasFactory;
    protected $table = 'requests';
    protected $fillable = ['sales_id', 'team_lead_id', 'message', 'status', 'approved_by'];

    // علاقة الطلب مع موظف المبيعات
    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    // علاقة الطلب مع التيم ليدر
    public function teamLead()
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
