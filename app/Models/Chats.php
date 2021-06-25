<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chats extends Model
{
    use HasFactory;
    protected $fillable = [
        'chat_id',
        'first_name',
        'last_name',
        'username',
        'referred_by',
        'twitter_link',
        'twitter_profile_link',
        'ammount_referred',
        'coin_address',
        'ammount_earned_from_referral'
    ];

    // protected $with = ['referral'];

    public function referral () {
        return $this->belongsTo(Chats::class, 'referred_by', 'chat_id');
    }
}
