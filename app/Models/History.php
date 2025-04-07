<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = ['request_id', 'user_id', 'action'];

    public function request()
    {
        return $this->belongsTo(TeamLeadRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
