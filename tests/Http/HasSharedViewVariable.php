<?php
namespace Laranix\Tests\Http;

trait HasSharedViewVariable
{
    /**
     * Check for shared variables in view
     *
     * @param array ...$vars
     * @return bool
     */
    protected function hasSharedViewVariables(...$vars)
    {
        $view = $this->app->make('view');

        $shared = $view->getShared();

        $isShared = true;

        foreach ($vars as $var) {
            if (!isset($shared[$var])) {
                $isShared = false;
            }
        }

        return $isShared;
    }
}
