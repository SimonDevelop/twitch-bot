<?php

namespace App\Commands;

use App\Services\TwitchApi;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;
use Laracord\Laracord;

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
    protected $admin = false;

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
    public function handle($message, $args)
    {
        $result = $this->api->getSubscriptions();
        $text = "";
        $first = true;
        foreach ($result['data'] as $sub) {
            if ($first) {
                $first = false;
                $text .= "\n\n";
            }
            $text .= "Subscription ID : " . $sub['id'] . "\n";
            $text .= "Channel ID : " . $sub['condition']['broadcaster_user_id'] . "\n";
            $text .= "Status : " . $sub['status'];
        }

        if ($text === "") {
            $text = "Aucun channel dans la liste.";
        }
        return $this
            ->message()
            ->title('Liste des subscriptions')
            ->content($text)
            ->send($message);
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            'wave' => fn (Interaction $interaction) => $this->message('👋')->reply($interaction),
        ];
    }
}
