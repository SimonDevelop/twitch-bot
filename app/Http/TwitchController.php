<?php

namespace App\Http;

use App\Services\TwitchApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laracord\Http\Controllers\Controller;

class TwitchController extends Controller
{
    public function index(Request $request, TwitchApi $api): Response
    {
        if (!$api->validateSignature($request)) {
            throw new \Exception("Invalid webhook signature");
        }

        $ifVerification = $request->headers->get('Twitch-Eventsub-Message-Type') === 'webhook_callback_verification';
        $content = json_decode($request->getContent(), true);

        if ($ifVerification) {
            return new Response($content['challenge']);
        }

        if ($content['subscription']['type'] === 'stream.online') {
            return new Response('', 204);
        }

        throw new \Exception("Unhandled webhook event " . $request->headers->get('Twitch-Eventsub-Subscription-Type'));
    }
}
