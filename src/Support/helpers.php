<?php

use Laranix\Support\IO\Url\Url;

if (!function_exists('url_create')) {
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
    function url_create(?string $scheme = null, ?string $domain = null, $path = null, ?array $query = [], ?string $fragment = null, bool $trailingSlash = false) : string
    {
        return Url::create($scheme, $domain, $path, $query, $fragment, $trailingSlash);
    }
}

if (!function_exists('url_to')) {
    /**
     * Create a local url appended to the app url.
     *
     * @param string|array|null $path
     * @param array|null        $query
     * @param null|string       $fragment
     * @param bool              $trailingSlash
     * @return string
     */
    function url_to($path = null, ?array $query = [], ?string $fragment = null, bool $trailingSlash = false) : string
    {
        return Url::to($path, $query, $fragment, $trailingSlash);
    }
}

if (!function_exists('url_href')) {
    /**
     * Create an HTML a tag
     *
     * @param string $url
     * @param array  $params
     * @return string
     */
    function url_href(string $url, string $content, array $params = []) : string
    {
        return Url::href($url, $content, $params);
    }
}

if (!function_exists('model_diff')) {
    /**
     * Works out differences between 2 model arrays and returns changed values
     *
     * @param array $old
     * @param array $new
     * @param bool  $json_encode
     * @param array $ignore
     * @return array|string
     */
    function model_diff(array $old, array $new, bool $json_encode = true, array $ignore = ['created_at', 'updated_at', 'deleted_at'])
    {
        $diff = [];

        $ignore = array_flip($ignore);

        foreach ($new as $key => $value) {
            if (isset($ignore[$key])) {
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
