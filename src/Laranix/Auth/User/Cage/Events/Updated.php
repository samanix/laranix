<?php
namespace Laranix\Auth\User\Cage\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\Cage\Cage;

class Updated
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\User\Cage\Cage
     */
    public $cage;

    /**
     * @var \Laranix\Auth\User\Cage\Cage
     */
    public $oldcage;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\User\Cage\Cage $cage
     * @param \Laranix\Auth\User\Cage\Cage $oldcage
     */
    public function __construct(Cage $cage, Cage $oldcage)
    {
        $this->cage     = $cage;
        $this->oldcage  = $oldcage;
    }
}
