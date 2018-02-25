<?php
namespace Laranix\AntiSpam\Middleware;

use Closure;
use Laranix\AntiSpam\Sequence\Sequence;
use Laranix\AntiSpam\Recaptcha\Recaptcha;
use Laranix\Support\Request\Middleware;

class Verify extends Middleware
{
    /**
     * Handle request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param bool                     $recaptcha
     * @return mixed
     */
    public function handle($request, Closure $next, bool $recaptcha = true)
    {
        if ($this->shouldExecute($request)) {
            return $this->before($request, $next, $recaptcha);
        }

        return $next($request);
    }

    /**
     * Process middleware.
     * Write this as you would a normal handle method for laravel middleware
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param bool                     $recaptcha
     * @return mixed
     */
    protected function before($request, Closure $next, bool $recaptcha = true)
    {
        $sequence   = $this->app->make(Sequence::class);

        if (!$sequence->verify()) {
            return $sequence->redirect();
        }

        if ($recaptcha) {
            $recaptcha = $this->app->make(Recaptcha::class);

            if (!$recaptcha->verify()) {
                return $recaptcha->redirect();
            }
        }

        return $next($request);
    }
}
