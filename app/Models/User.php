<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mob',
        'email',
        'password',
        'role_id',
        'company_id',
        'active',
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
        'active' => 'boolean',
    ];

    /**
     * Get the role this user belongs to
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class);
    }

    /**
     * Get the company this user belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class);
    }

    /**
     * Get all leads assigned to this user
     */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Leads::class, 'assigned_to');
    }

    /**
     * Get all tasks assigned to this user
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get all attendances for this user
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendances::class);
    }

    /**
     * Get all FCM registrations for this user
     */
    public function fcmRegistrations(): HasMany
    {
        return $this->hasMany(Fcmregs::class);
    }
}
