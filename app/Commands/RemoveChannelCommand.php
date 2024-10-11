<?php

namespace App\Commands;

use App\Services\TwitchApi;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;
use Laracord\Laracord;

class RemoveChannelCommand extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'remove';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'The remove subscription twitch channel Command.';

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
        if (count($args) > 0) {
            foreach ($args as $arg) {
                if ($this->api->removeSubscriptions($arg) === false) {
                    return $this
                        ->message()
                        ->title("Une erreur est survenue lors de l'ajout du channel : " . $arg)
                        ->send($message);
                }
            }

            return $this
                ->message("Suppression terminé avec succès !")
                ->send($message);
        }

        return $this
            ->message("Aucun id channel dans la commande.")
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
