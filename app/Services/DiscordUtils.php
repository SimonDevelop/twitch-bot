<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Laracord\Laracord;

class DiscordUtils
{
    private string $channelAnnouncement;
    private TwitchApi $api;
    private Laracord $bot;
    public function __construct()
    {
        $this->channelAnnouncement = Config::get('discord.discord_channel_id');
        $this->api = new TwitchApi();
        $this->bot = app('bot');
    }

    public function sendAnnouncement(string $userId): void
    {
        $infos = $this->api->getChannelInformation($userId);
        $username = $infos['broadcaster_name'];
        $url = 'https://www.twitch.tv/' . $username;

        $this->bot->message($username . " est en live !\n" . $url)->send($this->channelAnnouncement);
    }
}
