<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Marketing extends Model
{
    use HasFactory;
    protected $fillable = ['project_name', 'project_type', 'start_date', 'end_date', 'project_leader', 'support', 'summary', 'cost', 'profit_margin'];


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'marketing_users', 'marketing_id', 'user_id');
    }


}
