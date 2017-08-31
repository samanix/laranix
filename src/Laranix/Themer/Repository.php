<?php
/**
 * Created by PhpStorm.
 * User: Sam
 * Date: 2017-03-15
 * Time: 14:34
 */
namespace Laranix\Themer;

interface Repository
{
    /**
     * Load all themes
     *
     * @return array
     */
    public function load();

    /**
     * Get all themes
     *
     * @return array
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function all(): array;

    /**
     * Add a theme if enabled
     *
     * @param \Laranix\Themer\ThemeSettings $settings
     * @param string|null                   $key
     * @return \Laranix\Themer\Theme|null
     * @throws \Laranix\Support\Exception\KeyExistsException
     */
    public function add(ThemeSettings $settings, string $key = null): ?Theme;

    /**
     * Get a theme
     *
     * @param string $key
     * @param bool   $default
     * @return \Laranix\Themer\Theme
     */
    public function get(string $key, bool $default = true): Theme;

    /**
     * Check if theme exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Set default theme
     *
     * @param string $theme
     * @return \Laranix\Themer\Theme
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function setDefault($theme) : Theme;

    /**
     * Get default theme
     *
     * @return \Laranix\Themer\Theme
     */
    public function getDefault(): Theme;

    /**
     * Check if there is an override theme
     *
     * @return bool
     */
    public function hasOverride(): bool;

    /**
     * Set override theme
     *
     * @param string|Theme $theme
     * @return \Laranix\Themer\Theme|null
     */
    public function setOverride($theme): ?Theme;

    /**
     * Get override theme
     *
     * @return \Laranix\Themer\Theme|null
     */
    public function getOverride(): ?Theme;
}
