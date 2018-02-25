<?php
namespace Laranix\AntiSpam\Middleware;

use Closure;
use Laranix\AntiSpam\Sequence\Sequence;
use Laranix\AntiSpam\Recaptcha\Recaptcha;
use Laranix\Support\Request\Middleware;

class Verify extends Middleware
{
    /**
     * @inheritDoc
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
