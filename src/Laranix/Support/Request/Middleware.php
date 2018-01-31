<?php
namespace Laranix\Support\Request;

use Illuminate\Contracts\Foundation\Application;
use Closure;

class Middleware
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Excluded URIs for before middleware.
     *
     * @var array
     */
    protected $beforeExcept = [];

    /**
     * Excluded URI for after middleware
     *
     * @var array
     */
    protected $afterExcept = [];

    /**
     * LaranixMiddleware constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldExecute($request)) {
            return $this->before($request, $next);
        }

        return $next($request);
    }


    /**
     * Process before middleware.
     * Write this as you would a normal handle method for laravel middleware
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    protected function before($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Terminating middleware
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function terminate($request, $response)
    {
        if ($this->shouldExecute($request, false)) {
            $this->after($request, $response);
        }

        return;
    }

    /**
     * Process after middleware
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function after($request, $response)
    {
        return;
    }

    /**
     * Determine if request has a URI that should fire the after middleware
     *
     * @param \Illuminate\Http\Request $request
     * @param bool                     $before
     * @return bool
     */
    protected function shouldExecute($request, bool $before = true): bool
    {
        return !in_array($request->path(), $before ? $this->beforeExcept : $this->afterExcept);
    }
}
