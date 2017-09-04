<?php
namespace Laranix\Themer;

use Illuminate\Contracts\Config\Repository as Config;
use Laranix\Support\Exception\KeyExistsException;
use Laranix\Support\Exception\NullValueException;

class ThemeRepository implements Repository
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $themes;

    /**
     * @var \Laranix\Themer\Theme
     */
    protected $default;

    /**
     * @var \Laranix\Themer\Theme
     */
    protected $override;

    /**
     * Repository constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param bool                                    $load
     */
    public function __construct(Config $config, bool $load = true)
    {
        $this->config   = $config;

        if ($load) {
            $this->load();
        }
    }

    /**
     * Load all themes
     *
     * @return array
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function load()
    {
        $themes = $this->config->get('themer.themes');

        if ($themes === null) {
            throw new NullValueException('Theme list is empty');
        }

        return $this->parseThemes($themes);
    }

    /**
     * Get all themes
     *
     * @return array
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function all() : array
    {
        if ($this->themes !== null) {
            return $this->themes;
        }

        return $this->themes = $this->load();
    }

    /**
     * Add a theme if enabled
     *
     * @param \Laranix\Themer\ThemeSettings $settings
     * @param string|null                   $key
     * @return \Laranix\Themer\Theme|null
     * @throws \Laranix\Support\Exception\KeyExistsException
     */
    public function add(ThemeSettings $settings, string $key = null) : ?Theme
    {
        if ($key !== null) {
            $settings->key = $key;
        }

        if ($this->has($settings->key)) {
            throw new KeyExistsException("Theme with key '{$settings->key}' already exists");
        }

        if ($settings->enabled) {
            $theme = new Theme($settings);

            if ($settings->default === true && $this->default === null) {
                $this->setDefault($theme);
            }

            if ($settings->override === true && $this->override === null) {
                $this->setOverride($theme);
            }

            $this->themes[$settings->key] = $theme;
        }

        return $theme ?? null;
    }

    /**
     * Get a theme
     *
     * @param string $key
     * @param bool   $default
     * @return \Laranix\Themer\Theme
     */
    public function get(?string $key = null, bool $default = true) : Theme
    {
        if ($this->has($key)) {
            return $this->themes[$key];
        }

        if ($default && $this->default !== null) {
            return $this->default;
        }

        throw new \InvalidArgumentException("Theme '{$key}' does not exist in themes, default fallback not found either");
    }

    /**
     * Parse themes
     *
     * @param array $themes
     * @return array
     */
    protected function parseThemes(array $themes) : array
    {
        foreach ($themes as $key => $theme) {
            $this->add(new ThemeSettings($theme), $theme['key'] ?? $key);
        }

        if ($this->default === null) {
            /** @var Theme $theme */
            foreach ($this->themes as $theme) {
                if ($theme->verified()) {
                    $this->setDefault($theme);
                    break;
                }
            }

            if ($this->default === null) {
                throw new \InvalidArgumentException('No verifiable themes found to set as default');
            }
        }

        return $this->themes;
    }

    /**
     * Check if theme exists
     *
     * @param string $key
     * @return bool
     */
    public function has(?string $key) : bool
    {
        return $key === null ? false : isset($this->themes[$key]);
    }

    /**
     * Set default theme
     *
     * @param string $theme
     * @return \Laranix\Themer\Theme
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function setDefault($theme) : Theme
    {
        if ($theme instanceof Theme) {
            $default = $theme;
        } elseif ($this->has($theme)) {
            $default = $this->get($theme);
        }

        if (!isset($default)) {
            throw new NullValueException("Cannot set default theme when theme does not exist");
        }

        if (!$default->verified() && !runningCli()) {
            throw new \InvalidArgumentException("Cannot set default theme to an unverified theme");
        }

        return $this->default = $default;
    }

    /**
     * Get default theme
     *
     * @return \Laranix\Themer\Theme
     */
    public function getDefault() : Theme
    {
        return $this->default;
    }

    /**
     * Check if there is an override theme
     *
     * @return bool
     */
    public function hasOverride() : bool
    {
        return $this->override !== null;
    }

    /**
     * Set override theme
     *
     * @param string|Theme $theme
     * @return \Laranix\Themer\Theme|null
     */
    public function setOverride($theme = null) : ?Theme
    {
        if ($theme === null) {
            return $this->override = null;
        }

        if ($theme instanceof Theme) {
            $this->override = $theme;
        } elseif ($this->has($theme)) {
            $this->override = $this->get($theme);
        }

        if ($this->override === null || ($this->override !== null && !$this->override->verified())) {
            $this->override = $this->default;
        }

        return $this->override;
    }

    /**
     * Get override theme
     *
     * @return \Laranix\Themer\Theme|null
     */
    public function getOverride() : ?Theme
    {
        return $this->override;
    }
}
