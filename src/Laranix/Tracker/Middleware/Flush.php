<?php
namespace Laranix\Tracker\Middleware;

use Laranix\Support\Middleware;
use Laranix\Tracker\Writer;

class Flush extends Middleware
{
    /**
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     */
    public function after($request, $response)
    {
        if ($this->app->make('config')->get('tracker.enabled') !== true) {
            return;
        }

        $tracker = $this->app->make(Writer::class);

        $tracker->flush();
    }
}
