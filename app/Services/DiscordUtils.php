<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Support\Facades\Config;
use Laracord\Laracord;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;

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

    public function sendAnnouncement(string $userId, string $event): null|ExtendedPromiseInterface|PromiseInterface
    {
        $channel = Channel::where('twitch_id', $userId)->first();
        $change = false;
        if ($channel->state !== $event) {
            $change = true;
            $channel->state = $event === 'stream.online' ? 'stream.offline' : 'stream.online';
            $channel->save();
        }

        if ($change && $channel->state === 'stream.online') {
            try {
                $infos = $this->api->getChannelInformation($userId);
                $username = $infos['broadcaster_name'];
                $url = 'https://www.twitch.tv/' . strtolower($username);
                $userInfos = $this->api->getUserInformation($username);
                $liveInfos = $this->api->getStreamInformation($userInfos['id']);
                $thumbnail = str_replace([
                    "{width}",
                    "{height}"
                ], [
                    "400",
                    "225"
                ], $liveInfos['thumbnail_url']);

                $this->bot->message()
                    ->title($liveInfos['title'])
                    ->url($url)
                    ->authorName($username . ' est en live sur Twitch !')
                    ->authorIcon('')
                    ->authorUrl($url)
                    ->field('Catégorie', $liveInfos['game_name'])
                    ->field('Viewers', $liveInfos['viewer_count'])
                    ->thumbnailUrl($userInfos['profile_image_url'])
                    ->imageUrl($thumbnail)
                    ->button('Regarder le stream', $url)
                    ->send($this->channelAnnouncement);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }
}
