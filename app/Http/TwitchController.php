<?php

namespace App\Http;

use App\Services\DiscordUtils;
use App\Services\TwitchApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laracord\Http\Controllers\Controller;

class TwitchController extends Controller
{
    public function index(Request $request, TwitchApi $api, DiscordUtils $discordUtils): Response
    {
        if (!$api->validateSignature($request)) {
            throw new \Exception("Signature du webhook incorrect");
        }

        $ifVerification = $request->headers->get('Twitch-Eventsub-Message-Type') === 'webhook_callback_verification';
        $body = json_decode($request->getContent(), true);
        if ($ifVerification) {
            return new Response($body['challenge']);
        }

        if ($body['subscription']['type'] === 'stream.online' && isset($body['subscription']['condition']['broadcaster_user_id'])) {
            $discordUtils->sendAnnouncement($body['subscription']['condition']['broadcaster_user_id']);

            return new Response('', 204);
        }

        throw new \Exception("Unhandled webhook event " . $request->headers->get('Twitch-Eventsub-Subscription-Type'));
    }
}
