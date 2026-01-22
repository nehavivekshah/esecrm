<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Companies extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'mob', 'email', 'img', 'gst',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Clients::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Projects::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoices::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
