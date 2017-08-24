<?php
namespace Laranix\Support\IO\Url;

abstract class UrlCreator
{
    /**
     * App url
     *
     * @var string
     */
    protected $appUrl;

    /**
     * Cached urls
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Url constructor.
     *
     * @param $appUrl
     */
    public function __construct(?string $appUrl)
    {
        $this->appUrl = $appUrl ?? $this->getAppUrl();
    }

    /**
     * Create cache key
     *
     * @param array ...$settings
     * @return string
     */
    protected function getCacheKey(...$settings)
    {
        return hash('crc32', json_encode($settings));
    }

    /**
     * Check for cached item
     *
     * @param string $key
     * @return bool
     */
    protected function hasCachedData(string $key) : bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * Get cached item
     *
     * @param string $key
     * @return string|null
     */
    protected function getCachedData(string $key) : ?string
    {
        return $this->cache[$key] ?? null;
    }

    /**
     * Add an item to the cache
     *
     * @param string $key
     * @param string $data
     * @return string
     */
    protected function cacheData(string $key, string $data) : string
    {
        $this->cache[$key] = $data;

        return $data;
    }


    /**
     * Try to get app url
     *
     * @return null|string
     */
    protected function getAppUrl() : ?string
    {
        return $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? null;
    }
}
