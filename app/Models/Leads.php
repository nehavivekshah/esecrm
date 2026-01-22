<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leads extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'cid', 'uid', 'name', 'company', 'email', 'mob', 'gstno', 
        'location', 'purpose', 'assigned', 'poc', 'status', 
        'whatsapp', 'position', 'industry', 'website', 'values', 
        'language', 'tags'
    ];
}
