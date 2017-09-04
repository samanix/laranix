<?php
namespace Laranix\Support\IO\Url;

use Laranix\Support\Settings;

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
     * Create and return string output
     *
     * @param \Laranix\Support\Settings $settings
     * @return string
     */
    abstract protected function createOutput(Settings $settings) : string;

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
     * Make url string
     *
     * @param \Laranix\Support\Settings $settings
     * @return string
     */
    public function make(Settings $settings) : string
    {
        $cacheKey = $this->getCacheKey($settings);

        if ($this->hasCachedData($cacheKey)) {
            return $this->getCachedData($cacheKey);
        }

        $settings->hasRequiredSettings();

        return $this->cacheData($cacheKey, $this->createOutput($settings));
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
