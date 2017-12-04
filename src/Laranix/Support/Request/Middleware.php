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

        $this->beforeExcept = !empty($this->beforeExcept) ? array_flip($this->beforeExcept) : $this->beforeExcept;
        $this->afterExcept = !empty($this->afterExcept) ? array_flip($this->afterExcept) : $this->afterExcept;
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
        if ($this->shouldExecuteBefore($request)) {
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
        if ($this->shouldExecuteAfter($request)) {
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
     * Determine if request has a URI that should fire the before middleware
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldExecuteBefore($request) : bool
    {
        if (empty($this->beforeExcept)) {
            return true;
        }

        return !isset($this->beforeExcept[$request->path()]);
    }

    /**
     * Determine if request has a URI that should fire the after middleware
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldExecuteAfter($request) : bool
    {
        if (empty($this->afterExcept)) {
            return true;
        }

        return !isset($this->afterExcept[$request->path()]);
    }
}
