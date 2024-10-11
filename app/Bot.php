<?php

namespace App;

use Illuminate\Support\Facades\Route;
use Laracord\Laracord;

class Bot extends Laracord
{
    /**
     * The HTTP routes.
     */
    public function routes(): void
    {
        Route::middleware([])->group(function () {
            Route::post('/twitch/webhook', 'App\Http\TwitchController@index');
        });
    }
}
