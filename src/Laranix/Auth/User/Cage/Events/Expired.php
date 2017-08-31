<?php
namespace Laranix\Auth\User\Cage\Events;

use Illuminate\Queue\SerializesModels;
use Laranix\Auth\User\Cage\Cage;

class Expired
{
    use SerializesModels;

    /**
     * @var \Laranix\Auth\User\Cage\Cage
     */
    public $cage;

    /**
     * Create a new event instance.
     *
     * @param \Laranix\Auth\User\Cage\Cage $cage
     */
    public function __construct(Cage $cage)
    {
        $this->cage     = $cage;
    }
}
