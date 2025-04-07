<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
       'role_id',
       'department_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    protected $with = ['roles'];

    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(Developer::class, 'developer_users', 'user_id', 'developer_id');
    }
    public function marketings(): BelongsToMany
    {
        return $this->belongsToMany(Marketing::class, 'marketing_users', 'user_id', 'marketing_id');
    }

    // public function department()
    // {
    //     return $this->belongsTo(Department::class, 'department_id');
    // }

    public function role()
{
    return $this->belongsTo(Role::class);
}

public function department()
{
    return $this->belongsTo(Department::class, 'department_id');
}


public function reports()
{
    return $this->hasMany(Report::class, 'user_id');
}

}
