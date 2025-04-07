<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalProject extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'start_date',
        'end_date',
        'summary',
        'cost'
    ];
}
