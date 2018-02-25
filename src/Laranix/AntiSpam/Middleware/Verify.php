<?php
namespace Laranix\AntiSpam\Middleware;

use Closure;
use Laranix\AntiSpam\Sequence\Sequence;
use Laranix\AntiSpam\Recaptcha\Recaptcha;
use Laranix\Support\Request\Middleware;

class Verify extends Middleware
{
    /**
     * Process middleware.
     * Write this as you would a normal handle method for laravel middleware
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param bool                     $recaptcha
     * @return mixed
     */
    protected function before($request, Closure $next)
    {
        $sequence   = $this->app->make(Sequence::class);

        if (!$sequence->verify()) {
            return $sequence->redirect();
        }

        if ($request->session()->pull('__recaptcha_active') === true) {
            $recaptcha = $this->app->make(Recaptcha::class);

            if (!$recaptcha->verify()) {
                return $recaptcha->redirect();
            }
        }

        return $next($request);
    }
}
