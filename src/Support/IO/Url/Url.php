<?php
namespace Laranix\Support\IO\Url;

use Illuminate\Support\Str;
use Laranix\Support\IO\Str\Str as StrFormat;
use Laranix\Support\IO\Str\Settings as StrSettings;
use Laranix\Support\Settings;

class Url extends UrlCreator
{
    /**
     * Create a url from variable type
     *
     * @param $url
     * @return null|string
     */
    public function url($url)
    {
        if (is_string($url)) {
            return $this->parseStringUrl($url);
        }

        if ($url instanceof UrlSettings) {
            return $this->make($url);
        }

        throw new \InvalidArgumentException('Settings is not a supported type');
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
     * @return string|null
     */
    public function create(?string $scheme = null,
                           ?string $domain = null,
                           $path = null,
                           ?array $query = [],
                           ?string $fragment = null,
                           bool $trailingSlash = false) : string
    {
        return $this->make(new UrlSettings([
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
    public function to($path = null,
                       ?array $query = [],
                       ?string $fragment = null,
                       bool $trailingSlash = false) : string
    {
        return $this->create(null, $this->appUrl, $path, $query, $fragment, $trailingSlash);
    }

    /**
     * Current Url
     *
     * @return string
     */
    public function self() : string
    {
        return $this->parseStringUrl($_SERVER['REQUEST_URI'] ?? null);
    }


    /**
     * Parse url when given as string
     *
     * @param string $url
     * @return string
     */
    protected function parseStringUrl(?string $url) : string
    {
        $parts = parse_url($url);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        return $this->create(
            $parts['scheme'] ?? null,
            $parts['host'] ?? null,
            $parts['path'] ?? null,
            $query ?? null,
            $parts['fragment'] ?? null,
            (Str::endsWith($url, '/')
                && (isset($parts['path'])
                    && ($parts['path'] !== '/' || !empty($parts['path']))) )
        );
    }

    /**
     * Create the URL string
     *
     * @param \Laranix\Support\Settings|\Laranix\Support\IO\Url\UrlSettings $settings
     * @return string
     */
    protected function createOutput(Settings $settings) : string
    {
        return StrFormat::format(
            '{{scheme}}{{domain}}{{path}}{{query}}{{fragment}}',
            $this->parseUrlComponents($settings),
            new StrSettings([ 'removeUnparsed' => true ])
        );
    }

    /**
     * Parse the parts of the Url
     *
     * @param \Laranix\Support\IO\Url\UrlSettings $settings
     * @return array
     */
    protected function parseUrlComponents(UrlSettings $settings) : array
    {
        return [
            'scheme'    => $this->getScheme($settings->scheme, $settings->domain),
            'domain'    => $this->getDomain($settings->domain),
            'path'      => $this->getPath($settings->path, $settings->trailingSlash),
            'query'     => $this->getQueryString($settings->query),
            'fragment'  => $this->getFragment($settings->fragment),
        ];
    }

    /**
     * Get url scheme
     *
     * @param string|null $scheme
     * @param null|string $domain
     * @return string
     */
    protected function getScheme(?string $scheme, ?string $domain = null) : string
    {
        if ($scheme === null || $scheme === '//') {
            return $this->tryGuessScheme($domain);
        }

        return $this->trim($scheme, ':/') . '://';
    }

        /**
     * Get the scheme from the domain
     *
     * @param null|string $domain
     * @return null|string
     */
    protected function tryGuessScheme(?string $domain) : ?string
    {
        if ($domain !== null) {
            if (Str::startsWith($domain, 'https')) {
                return 'https://';
            }

            if (Str::startsWith($domain, 'http')) {
                return 'http://';
            }
        }

        return (isset($_SERVER['HTTPS'])
            && !empty($_SERVER['HTTPS'])
            && strtolower($_SERVER['HTTPS']) !== 'off')
            ? 'https://' : 'http://';
    }

    /**
     * Get domain
     *
     * @param null|string $domain
     * @return string|null
     */
    protected function getDomain(?string $domain = null) : ?string
    {
        $domain = $domain ?? $this->appUrl;

        if ($domain === null) {
            return null;
        }

        return $this->trim(str_replace(['http:', 'https:'], '', $domain), '/');
    }

    /**
     * Get path
     *
     * @param mixed $path
     * @param bool  $trailing
     * @return null|string
     */
    protected function getPath($path, bool $trailing = false) : ?string
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

        $path = implode('/', array_map(function ($item) {
            return rawurlencode($this->trim($item, '/'));
        }, $path));

        return '/'. $path . $this->getTrailingSlash($last, $trailing);
    }

    /**
     * Build query string
     *
     * @param array|null $query
     * @return null|string
     */
    protected function getQueryString(?array $query) : ?string
    {
        if (empty($query)) {
            return null;
        }

        return '?' . http_build_query($query);
    }

    /**
     * Get url fragment
     *
     * @param null|string $fragment
     * @return null|string
     */
    protected function getFragment(?string $fragment) : ?string
    {
        if ($fragment === null) {
            return null;
        }

        return '#' . $this->trim($fragment, '#');
    }

    /**
     * Check if we should include a trailing slash
     *
     * @param string $item
     * @param bool   $trailing
     * @return string
     */
    protected function getTrailingSlash(string $item, bool $trailing) : string
    {
        if (Str::contains($item, '.') || !$trailing) {
            return '';
        }

        return '/';
    }

    /**
     * Trim string
     *
     * @param string      $string
     * @param null|string $extra
     * @return string
     */
    protected function trim(string $string, ?string $extra = null)
    {
        return trim($string, ($extra ?? '') . " \t\n\r\0\x0B");
    }
}
