<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mob',
        'company',
        'lifecycle_stage',
        'industry',
        'website',
        'location',
        'status',
        'poc',
        'whatsapp',
        'position',
        'values',
        'language'
    ];

    public function departments()
    {
        return $this->hasMany(CustomerDepartments::class, 'client_id');
    }
}
