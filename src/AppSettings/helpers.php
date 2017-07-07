<?php

if (!function_exists('app_setting')) {
    /**
     * Helper function for getting network link.
     *
     * @param string $key
     * @param null   $default
     * @return string
     */
    function app_setting(string $key, $default = null)
    {
        return app(\Laranix\AppSettings\AppSettings::class)->get($key, $default);
    }
}
