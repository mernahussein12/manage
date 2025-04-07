<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'status', 'changed_by'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
