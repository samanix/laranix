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
        $tracker = $this->app->make(Writer::class);

        $tracker->flush();
    }
}
