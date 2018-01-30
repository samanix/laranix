<?php
namespace Laranix\Auth\Password\Listeners;

use Illuminate\Contracts\Config\Repository;
use Laranix\Auth\Password\Events\Settings;
use Laranix\Auth\Password\Events\Updated as UpdatedEvent;
use Laranix\Session\Session;
use Laranix\Support\Listeners\Listener;
use Laranix\Tracker\TrackWriter;

class Updated extends Listener
{
    /**
     * @var string
     */
    protected $type = 'password';

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Listener constructor.
     *
     * @param \Laranix\Tracker\TrackWriter            $writer
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(TrackWriter $writer, Repository $config)
    {
        parent::__construct($writer);

        $this->config = $config;
    }

    /**
     * Handle event
     *
     * @param \Laranix\Auth\Password\Events\Updated $event
     */
    public function handle(UpdatedEvent $event)
    {
        // Delete sessions for user on password update
        if ($this->config->get('session.driver') === 'laranix') {
            Session::where('user_id', $event->user->id)->delete();
        }

        $this->track(Settings::TYPEID_UPDATED, $event->user->id, 50);
    }
}
