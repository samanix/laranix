<?php
namespace Laranix\AppSettings;

use Illuminate\Contracts\Config\Repository as Config;

class AppSettings
{
    // TODO Static?

    /**
     * Main config
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $cached = [];

    /**
     * AppSettings constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (isset($this->cached[$key])) {
            return $this->cached[$key];
        }

        return $this->cached[$key] = $this->config->get("appsettings.{$key}", $default);
    }

    /**
     * Get app name.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->get('name', 'Laranix');
    }

    /**
     * Get version with prefix.
     *
     * @param string $prefix
     *
     * @return string|null
     */
    public function version(?string $prefix = 'v') : string
    {
        return ($prefix ?? '').$this->get('version', '1.0');
    }

    /**
     * Empty cache or cache value
     *
     * @param null|string $key
     */
    public function emptyCache(?string $key = null)
    {
        if ($key === null) {
            $this->cached = [];
        } elseif (isset($this->cached[$key])) {
            unset($this->cached[$key]);
        }
    }
}
