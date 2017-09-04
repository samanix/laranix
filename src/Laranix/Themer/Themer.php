<?php
namespace Laranix\Themer;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;

class Themer
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $cookie;

    /**
     * @var \Laranix\Themer\ThemeRepository
     */
    protected $themes;

    /**
     * @var \Laranix\Themer\Theme
     */
    protected $useTheme;

    /**
     * Themer constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Http\Request                $request
     * @param \Laranix\Themer\ThemeRepository         $themes
     */
    public function __construct(Config $config, Request $request, ThemeRepository $themes)
    {
        $this->config = $config;
        $this->request = $request;

        $this->cookie = $this->config->get('themer.cookie', 'laranix_theme');

        $this->themes = $themes;

        $this->setTheme();
    }

    /**
     * Set theme to use
     *
     * @param string|null $key
     * @param bool        $overrideKey
     * @return \Laranix\Themer\Theme
     */
    public function setTheme(?string $key = null, bool $overrideKey = false) : Theme
    {
        if ($this->useTheme !== null && ($key === null || $this->useTheme === $key)) {
            return $this->useTheme;
        }

        if ($overrideKey) {
            return $this->useTheme = $this->getTheme($key);
        }

        if ($this->themes->hasOverride()) {
            return $this->useTheme = $this->themes->getOverride();
        }

        if ($this->hasUserOverride()) {
            return $this->useTheme = $this->themes->setOverride($this->getUserOverride());
        }

        return $this->useTheme = $this->getTheme($key);
    }

    /**
     * Get a theme
     *
     * @param string|null $key
     * @return \Laranix\Themer\Theme
     */
    public function getTheme(?string $key = null) : Theme
    {
        if ($key === null && $this->useTheme !== null) {
            return $this->useTheme;
        }

        return $this->themes->get($key);
    }

    /**
     * Get default theme
     *
     * @return \Laranix\Themer\Theme
     */
    public function getDefaultTheme() : Theme
    {
        return $this->themes->getDefault();
    }

    /**
     * Check if user has override set
     *
     * @return bool
     */
    protected function hasUserOverride() : bool
    {
        return $this->request->hasCookie($this->cookie);
    }

    /**
     * Get user override theme key
     *
     * @return string
     */
    protected function getUserOverride() : string
    {
        return $this->request->cookie($this->cookie);
    }

    /**
     * Check if active theme is default theme
     *
     * @param Theme|string|null $key
     * @return bool
     */
    public function themeIsDefault($key = null) : bool
    {
        if ($key instanceof Theme) {
            return $key->getKey() === $this->getDefaultTheme()->getKey();
        }

        return $this->getTheme($key)->getKey() === $this->getDefaultTheme()->getKey();
    }
}
