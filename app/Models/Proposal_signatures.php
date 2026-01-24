<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal_signatures extends Model
{
    protected $table = 'proposal_signatures';

    protected $fillable = [
        'proposal_id',
        'token',
        'first_name',
        'last_name',
        'email',
        'signature_path',
    ];
}
