<?php

namespace App\Commands;

use App\Models\Channel;
use App\Services\TwitchApi;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;
use Laracord\Laracord;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;

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
        if (count($args) > 0) {
            foreach ($args as $arg) {
                $infos = $this->api->getUserInformation($arg);
                $channel = Channel::where('twitch_name', $infos['display_name'])->first();
                if ($this->api->removeSubscriptions($channel->subscription_id) === false) {
                    return $this
                        ->message()
                        ->title("Une erreur est survenue lors de l'ajout du channel : " . $arg)
                        ->send($message);
                }
                $channel->delete();
            }

            return $this
                ->message("Suppression terminé avec succès !")
                ->send($message);
        }

        return $this
            ->message("Aucun id channel dans la commande.")
            ->send($message);
    }
}
