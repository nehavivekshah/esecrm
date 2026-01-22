<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projects extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'company_id', 'name', 'description', 'start_date',
        'end_date', 'status', 'budget', 'progress',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'progress' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }
}
