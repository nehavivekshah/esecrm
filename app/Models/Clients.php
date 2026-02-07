<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;
    public function departments()
    {
        return $this->hasMany(CustomerDepartments::class, 'client_id');
    }
}
