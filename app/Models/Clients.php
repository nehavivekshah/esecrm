<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clients extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'email', 'phone', 'address', 'city',
        'state', 'country', 'postal_code', 'notes', 'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoices::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposals::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Projects::class);
    }
}
