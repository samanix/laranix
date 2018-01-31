<?php

use Laranix\Support\IO\Url\Url;
use Laranix\Support\IO\Url\Href;
use Laranix\Support\Settings;

if (!function_exists('urlUrl')) {
    /**
     * Create a url from variable type
     *
     * @param $url
     * @return null|string
     */
    function urlUrl($url): string
    {
        return app(Url::class)->url($url);
    }
}

if (!function_exists('urlMake')) {
    /**
     * Make url string
     *
     * @param \Laranix\Support\Settings $settings
     * @return string
     */
    function urlMake(Settings $settings): string
    {
        return app(Url::class)->make($settings);
    }
}

if (!function_exists('urlCreate')) {
    /**
     * Create a url
     *
     * @param string|null   $scheme
     * @param string|null   $domain
     * @param string|array  $path
     * @param array         $query
     * @param string|null   $fragment
     * @param bool          $trailingSlash
     *
     * @return string
     */
    function urlCreate(
        ?string $scheme = null,
        ?string $domain = null,
        $path = null,
        ?array $query = [],
        ?string $fragment = null,
        bool $trailingSlash = false
    ): string {
        return app(Url::class)->create($scheme, $domain, $path, $query, $fragment, $trailingSlash);
    }
}

if (!function_exists('urlTo')) {
    /**
     * Create a local url appended to the app url.
     *
     * @param string|array|null $path
     * @param array|null        $query
     * @param null|string       $fragment
     * @param bool              $trailingSlash
     * @return string
     */
    function urlTo(
        $path = null,
        ?array $query = [],
        ?string $fragment = null,
        bool $trailingSlash = false
    ): string {
        return app(Url::class)->to($path, $query, $fragment, $trailingSlash);
    }
}

if (!function_exists('urlSelf')) {
    /**
     * Current Url
     *
     * @return string
     */
    function urlSelf(): string
    {
        return app(Url::class)->self();
    }
}

if (!function_exists('hrefMake')) {
    /**
     * Make url string
     *
     * @param \Laranix\Support\Settings $settings
     * @return string
     */
    function hrefMake(Settings $settings): string
    {
        return app(Href::class)->make($settings);
    }
}

if (!function_exists('hrefCreate')) {
    /**
     * Create an HTML a tag
     *
     * @param string $content
     * @param mixed $url
     * @param array  $params
     * @return string
     */
    function hrefCreate(string $content, $url, array $params = []): string
    {
        return app(Href::class)->create($content, $url, $params);
    }
}

if (!function_exists('modelDiff')) {
    /**
     * Works out differences between 2 model arrays and returns changed values
     *
     * @param array $old
     * @param array $new
     * @param bool  $json_encode
     * @param array $ignore
     * @return array|string
     */
    function modelDiff(
        array $old,
        array $new,
        bool $json_encode = true,
        array $ignore = ['created_at', 'updated_at', 'deleted_at']
    ) {
        $diff = [];

        foreach ($new as $key => $value) {
            if (in_array($key, $ignore)) {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }

            if (!isset($old[$key])) {
                $diff[$key] = [null, $value];
                continue;
            }

            if (is_array($old[$key]) || is_object($old[$key])) {
                $old[$key] = json_encode($old[$key]);
            }

            if ($old[$key] !== $value) {
                $diff[$key] = [$old[$key], $value];
            }
        }

        return $json_encode ? json_encode($diff) : $diff;
    }
}

if (!function_exists('socialMedia')) {
    /**
     * Generate link to social media page
     *
     * @param string $key
     *
     * @return string|null
     */
    function socialMedia(string $key): ?string
    {
        $url = config("socialmedia.{$key}");

        if ($url === null) {
            return null;
        }

        return urlCreate(null, $url['url'] ?? null, $url['path'] ?? null);
    }
}

if (!function_exists('runningCli')) {
    /**
     * Check if app is running in cli
     *
     * @return bool
     */
    function runningCli(): bool
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

}
