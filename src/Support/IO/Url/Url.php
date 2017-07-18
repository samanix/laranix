<?php
namespace Laranix\Support\IO\Url;

use Illuminate\Support\Str;
use Laranix\Support\IO\Str\Str as StrFormat;
use Laranix\Support\IO\Str\Settings as StrSettings;

// TODO Allowed schemes array
class Url
{
    /**
     * @var string
     */
    protected static $appUrl;

    /**
     * @var array
     */
    protected static $cached = [];

    /**
     * @var array
     */
    protected static $cachedHref = [];

    /**
     * Generate a URL.
     *
     * @param \Laranix\Support\IO\Url\Settings|string $settings
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function url($settings) : string
    {
        $cacheKey = self::cacheKey($settings);

        if (self::hasCachedUrl($cacheKey)) {
            return self::getCachedUrl($cacheKey);
        } elseif (is_string($settings)) {
            $url = self::parseStringUrl($settings);
        } elseif ($settings instanceof Settings) {
            $url = StrFormat::format('{{scheme}}{{domain}}{{path}}{{query}}{{fragment}}',
                                     self::getUrlComponents($settings),
                                     new StrSettings([ 'removeUnparsed' => true ]));
        } else {
            throw new \InvalidArgumentException('Settings is not a supported type');
        }

        return self::cacheUrl($cacheKey, $url);
    }

    /**
     * Parse url when given as string
     *
     * @param string $url
     * @return string
     */
    protected static function parseStringUrl(?string $url) : string
    {
        $parts = array_replace([
            'scheme'    => null,
            'host'      => self::getAppUrl(),
            'path'      => null,
            'query'     => '',
            'fragment'  => null,
        ], (array) parse_url($url));

        parse_str($parts['query'], $query);

        return self::create($parts['scheme'],
                            $parts['host'],
                            $parts['path'],
                            $query,
                            $parts['fragment'],
                            (Str::endsWith($url, '/') && ($parts['path'] !== '/' || !empty($parts['path']))));
    }

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
    public static function create(?string $scheme = null, ?string $domain = null, $path = null, ?array $query = [], ?string $fragment = null, bool $trailingSlash = false) : string
    {
        return self::url(new Settings([
            'scheme'        => $scheme,
            'domain'        => $domain,
            'path'          => $path,
            'query'         => $query,
            'fragment'      => $fragment,
            'trailingSlash' => $trailingSlash,
        ]));
    }

    /**
     * Create a local url appended to the app url.
     *
     * @param string|array|null $path
     * @param array|null        $query
     * @param null|string       $fragment
     * @param bool              $trailingSlash
     * @return string
     */
    public static function to($path = null, ?array $query = [], ?string $fragment = null, bool $trailingSlash = false) : string
    {
        return self::create(null, self::getAppUrl(), $path, $query, $fragment, $trailingSlash);
    }

    /**
     * Current URL
     */
    public static function self() : string
    {
        return self::parseStringUrl($_SERVER['REQUEST_URI'] ?? null);
    }

    /**
     * HTML tagged URL
     *
     * @param string $url
     * @param string $content
     * @param array  $params
     * @return string
     */
    public static function href(string $url, string $content, array $params = []) : string
    {
        $cacheKey = self::cacheKey([$url, $content, $params]);

        if (self::hasCachedHref($cacheKey)) {
            return self::getCachedHref($cacheKey);
        }

        if (!empty($params)) {
            $extra = [];

            foreach ($params as $key => $param) {
                $extra[] = $key . '="' . $param . '"';
            }

            $properties = ' ' . implode(' ', $extra);
        }

        return self::cacheHref($cacheKey, sprintf('<a href="%s"%s>%s</a>', $url, $properties ?? '', $content));
    }

    /**
     * Get app url
     *
     * @return string
     */
    protected static function getAppUrl() : ?string
    {
        if (self::$appUrl !== null) {
            return self::$appUrl;
        }

        return self::$appUrl = config('app.url') ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? null;
    }

    /**
     * Parse URL parts
     *
     * @param \Laranix\Support\IO\Url\Settings|null $settings
     * @return array
     */
    protected static function getUrlComponents(?Settings $settings) : array
    {
        return [
            'scheme'    => self::getScheme($settings->scheme, $settings->domain),
            'domain'    => self::getDomain($settings->domain),
            'path'      => self::getPath($settings->path, $settings->trailingSlash),
            'query'     => self::getQuery($settings->query),
            'fragment'  => self::getFragment($settings->fragment),
        ];
    }

    /**
     * Get url scheme
     *
     * @param string|null $scheme
     * @param null|string $domain
     * @return string
     */
    protected static function getScheme(?string $scheme, ?string $domain = null) : string
    {
        if ($scheme === null || $scheme === '//') {
            return self::tryGuessScheme($domain);
        }

        return sprintf('%s://', trim($scheme, ":/ \t\n\r\0\x0B"));
    }

    /**
     * Get the scheme from the domain
     *
     * @return string
     */
    protected static function tryGuessScheme(?string $domain) : ?string
    {
        if (Str::startsWith($domain, 'https')) {
            return 'https://';
        }

        if (Str::startsWith($domain, 'http')) {
            return 'http://';
        }

        return (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https://' : 'http://';
    }

    /**
     * Get domain
     *
     * @param null|string $domain
     * @return string|null
     */
    protected static function getDomain(?string $domain = null) : ?string
    {
        $domain = $domain ?? self::getAppUrl();

        if ($domain === null) {
            return null;
        }

        return trim(str_replace(['http:', 'https:'], '', $domain), "/ \t\n\r\0\x0B");
    }

    /**
     * Get path
     *
     * @param mixed $path
     * @param bool  $trailing
     * @return string
     */
    protected static function getPath($path, bool $trailing = false) : ?string
    {
        if ($path === null || $path === '/') {
            return $trailing ? '/' : null;
        }

        if (is_string($path)) {
            $path = explode('/', $path);
        }

        $path = array_filter($path);

        if (empty($path)) {
            return $trailing ? '/' : null;
        }

        $last = end($path);

        $path = implode('/', array_map(function($item) {
            return rawurlencode(trim($item, "/ \t\n\r\0\x0B"));
        }, $path));



        return '/'. $path . self::getTrailingSlash($last, $trailing);
    }

    /**
     * Get query string
     *
     * @param array $query
     * @return null|string
     */
    protected static function getQuery(?array $query) : ?string
    {
        if ($query === null || empty($query)) {
            return null;
        }

        return '?'.http_build_query($query);
    }

    /**
     * Get fragment
     *
     * @param null|string $fragment
     * @return null|string
     */
    protected static function getFragment(?string $fragment) : ?string
    {
        if ($fragment === null) {
            return null;
        }

        return '#' . trim($fragment, "# \t\n\r\0\x0B");
    }

    /**
     * Check if last segment of path might be a file
     *
     * @param string $item
     * @param bool   $trailing
     * @return string
     */
    protected static function getTrailingSlash(string $item, bool $trailing) : string
    {
        if (Str::contains($item, '.') || !$trailing) {
            return '';
        }

        return '/';
    }

    /**
     * Cache key
     *
     * @param $settings
     * @return string
     */
    protected static function cacheKey($settings) : string
    {
        return hash('crc32', json_encode($settings));
    }

    /**
     * Check for cached url
     *
     * @param string $key
     * @return bool
     */
    protected static function hasCachedUrl(string $key) : bool
    {
        return isset(self::$cached[$key]);
    }

    /**
     * Get cached url
     *
     * @param string $key
     * @return string
     */
    protected static function getCachedUrl(string $key) : string
    {
        return self::$cached[$key];
    }

    /**
     * Store url in the cache
     *
     * @param string $key
     * @param string $url
     * @return string
     */
    protected static function cacheUrl($key, string $url) : string
    {
        self::$cached[$key] = $url;

        return $url;
    }

    /**
     * Check for cached href
     *
     * @param string $key
     * @return bool
     */
    protected static function hasCachedHref(string $key) : bool
    {
        return isset(self::$cached[$key]);
    }

    /**
     * Get cached href
     *
     * @param string $key
     * @return string
     */
    protected static function getCachedHref(string $key) : string
    {
        return self::$cached[$key];
    }

    /**
     * Store href in the cache
     *
     * @param string $key
     * @param string $url
     * @return string
     */
    protected static function cacheHref($key, string $url) : string
    {
        self::$cached[$key] = $url;

        return $url;
    }
}
