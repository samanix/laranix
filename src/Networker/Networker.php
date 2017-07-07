<?php
namespace Laranix\Networker;

use Illuminate\Contracts\Config\Repository;
use Laranix\Support\IO\Url\Url;
use Laranix\Support\IO\Url\Settings;

// TODO Magic method to retrieve by key?
class Networker
{
    /**
     * Stores loaded links.
     *
     * @var array
     */
    protected $loadedLinks = [];

    /**
     * Known host addresses.
     *
     * @var array
     */
    protected $knownHosts = [
        'facebook'  => 'https://facebook.com',
        'twitter'   => 'https://twitter.com',
        'instagram' => 'https://www.instagram.com',
        'bitbucket' => 'https://bitbucket.org',
        'github'    => 'https://github.com',
        'reddit'    => 'https://reddit.com/r',
    ];

    /**
     * NetworkLinks constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->loadLinks($config->get('networker'));
    }

    /**
     * Load links from config.
     *
     * @param array $links
     */
    protected function loadLinks(array $links)
    {
        // Parse known hosts
        foreach ($links['slugs'] as $key => $slug) {
            if (!isset($this->knownHosts[$key])) {
                continue;
            }

            $this->add($key, $this->knownHosts[$key], $slug);
        }

        // Parse custom links
        foreach ($links['links'] as $key => $link) {
            if (!isset($link['url']) || !isset($link['slug'])) {
                continue;
            }

            $this->add($key, $link['url'], $link['slug']);
        }
    }

    /**
     * Parse link.
     *
     * @param string|\Laranix\Support\IO\Url\Settings $url
     * @param string|null                             $slug
     * @return string
     */
    protected function parseLink($url, ?string $slug = null) : string
    {
        if ($url instanceof Settings) {
            return Url::url($url);
        }

        return Url::create(null, $url, $slug);
    }

    /**
     * Add a network link
     *
     * @param string                                  $key
     * @param string|\Laranix\Support\IO\Url\Settings $url
     * @param string|null                             $slug
     * @return string
     */
    public function add(string $key, $url, ?string $slug = null) : string
    {
        return $this->loadedLinks[$key] = $this->parseLink($url, $slug);
    }

    /**
     * Get link from stored list.
     *
     * @param string $key
     * @param bool   $includeTrailingSlash
     *
     * @return string|null
     */
    public function get(string $key, bool $includeTrailingSlash = false) : ?string
    {
        if (!isset($this->loadedLinks[$key])) {
            return null;
        }

        return $this->loadedLinks[$key].($includeTrailingSlash ? '/' : '');
    }
}
