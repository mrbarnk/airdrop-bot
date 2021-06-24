<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramRequests extends Model
{
    protected $fillables = ['user_id', 'request'];
    
    use HasFactory;
}
