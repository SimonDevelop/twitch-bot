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
        $url = 'https://www.twitch.tv/' . strtolower($username);
        $userInfos = $this->api->getUserInformation($username);
        $liveInfos = $this->api->getStreamInformation($userInfos['user_id']);
        $thumbnail = str_replace([
            "{width}",
            "{height}"
        ], [
            "300",
            "169"
        ], $liveInfos['thumbnail_url']);

        $this->bot->message()
            ->title($liveInfos['title'])
            ->url($url)
            ->authorName($username)
            ->authorIcon('')//$userInfos['profile_image_url']
            ->authorUrl($url)
            ->field('Catégorie', $liveInfos['game_name'])
            ->thumbnailUrl($userInfos['profile_image_url'])
            ->imageUrl($thumbnail)
            ->send($this->channelAnnouncement);
    }
}
