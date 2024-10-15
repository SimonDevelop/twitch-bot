<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'twitch_id',
        'twitch_name',
        'twitch_url',
        'subscription_online_id',
        'subscription_offline_id',
        'state'
    ];
}
