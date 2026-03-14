<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class CrmTask extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'user_id',
        'rel_type',
        'rel_id',
        'name',
        'type',
        'due_date',
        'status'
    ];
}
