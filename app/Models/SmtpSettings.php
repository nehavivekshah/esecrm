<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpSettings extends Model
{
    protected $fillable = [
        'cid', 'user_id', 'mailer', 'host', 'port', 'username',
        'password', 'encryption', 'from_address', 'from_name'
    ];
}
