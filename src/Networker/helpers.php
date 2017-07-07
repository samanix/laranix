<?php

if (!function_exists('networker')) {
    /**
     * Helper function for getting network link.
     *
     * @param string $key
     * @param bool   $includeTrailingSlash
     *
     * @return string
     */
    function networker(string $key, bool $includeTrailingSlash = false)
    {
        return app(\Laranix\Networker\Networker::class)->get($key, $includeTrailingSlash);
    }
}
