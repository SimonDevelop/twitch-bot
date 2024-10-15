<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class TwitchApi
{
    private string $client;
    private string $clientSecret;
    private string $webhookSecret;
    public function __construct()
    {
        $this->client = Config::get('discord.twitch_id');
        $this->clientSecret = Config::get('discord.twitch_secret');
        $this->webhookSecret = Config::get('discord.twitch_webhook_secret');
    }

    private function getAccessToken(): string
    {
        $response = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => $this->client,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ]);
        $token = json_decode($response->body(), true);

        return $token['access_token'];
    }

    public function getSubscriptions(): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->withHeader('Client-ID', $this->client)
            ->get('https://api.twitch.tv/helix/eventsub/subscriptions');

        $result = json_decode($response->body(), true);

        return $result;
    }

    public function createSubscriptions(string $streamId, string $event): bool|array
    {
        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->contentType('application/json')
            ->withHeader('Client-ID', $this->client)
            ->post('https://api.twitch.tv/helix/eventsub/subscriptions', [
                'type' => $event,
                'version' => '1',
                'condition' => [
                    'broadcaster_user_id' => $streamId
                ],
                'transport' => [
                    'method' => 'webhook',
                    'callback' => Config::get('discord.url_webhook') . '/twitch/webhook',
                    'secret' => $this->webhookSecret
                ]
            ]);

        if ($response->getStatusCode() > 300) {
            throw new \Exception('Cannot add twitch subscriptions : \n' . $response->body());
        }

        if ($response->getStatusCode() === 202) {
            return json_decode($response->body(), true);
        }

        return false;
    }

    public function removeSubscription(string $streamId): bool
    {
        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->contentType('application/json')
            ->withHeader('Client-ID', $this->client)
            ->delete('https://api.twitch.tv/helix/eventsub/subscriptions?id=' . $streamId);

        if ($response->getStatusCode() > 300) {
            return false;
        }

        return true;
    }

    public function getChannelInformation(string $channelId): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->withHeader('Client-ID', $this->client)
            ->get('https://api.twitch.tv/helix/channels?broadcaster_id=' . $channelId);

        $result = json_decode($response->body(), true);

        return $result['data'][0];
    }

    public function getUserInformation(string $username): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->withHeader('Client-ID', $this->client)
            ->get('https://api.twitch.tv/helix/users?login=' . strtolower($username));

        $result = json_decode($response->body(), true);

        return $result['data'][0];
    }

    public function getStreamInformation(string $id): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->withHeader('Client-ID', $this->client)
            ->get('https://api.twitch.tv/helix/streams?user_id=' . $id);

        $result = json_decode($response->body(), true);

        return $result['data'][0];
    }

    public function validateSignature(Request $request): bool|\Exception
    {
        $signature = $request->header('Twitch-Eventsub-Message-Signature');
        $messageId = $request->header('Twitch-Eventsub-Message-Id');
        $timestamp = $request->header('Twitch-Eventsub-Message-Timestamp');
        $content = $request->getContent();

        $message = $messageId . $timestamp . $content;
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $message, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }
}
