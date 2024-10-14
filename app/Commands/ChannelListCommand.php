<?php

namespace App\Commands;

use App\Models\Channel;
use App\Services\TwitchApi;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;
use Laracord\Laracord;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;

class ChannelListCommand extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'list';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'The list of subscription twitch channel Command.';

    /**
     * Determines whether the command requires admin permissions.
     *
     * @var bool
     */
    protected $admin = true;

    /**
     * Determines whether the command should be displayed in the commands list.
     *
     * @var bool
     */
    protected $hidden = false;

    private $api;

    public function __construct(Laracord $bot)
    {
        parent::__construct($bot);
        $this->api = new TwitchApi();
    }

    /**
     * Handle the command.
     *
     * @param  \Discord\Parts\Channel\Message  $message
     * @param  array  $args
     * @return void
     */
    public function handle($message, $args): null|ExtendedPromiseInterface|PromiseInterface
    {
        $channels = Channel::all();
        $result = $this->api->getSubscriptions();
        $text = "";
        $first = true;

        foreach ($result['data'] as $sub) {
            foreach ($channels as $channel) {
                if ($channel->twitch_id === $sub['condition']['broadcaster_user_id']) {
                    if ($first) {
                        $first = false;
                        $text .= "\n\n";
                    }
                    $text .= "Chaîne Twitch : " . $channel->twitch_name . "\n";
                    $text .= "Status : " . $sub['status'] . "\n";
                    $text .= "Ajouté le : " . (new \DateTime($sub['created_at']))->format("d/m/Y");
                }
            }
        }

        if ($text === "") {
            $text = "Aucune chaîne enregistrée.";
        }

        return $this
            ->message()
            ->title('Liste des chaînes enregistrées :')
            ->content($text)
            ->send($message);
    }
}
