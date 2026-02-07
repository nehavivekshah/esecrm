<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'gst_no',
        'email',
        'phone',
        'address'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }
}
