<?php
namespace Laranix\Themer\Image;

use Laranix\Support\Exception\NotImplementedException;
use Laranix\Themer\ResourceSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerResource;

class Image extends ThemerResource
{
    /**
     * Display an image
     *
     * @param string $image
     * @param array  $params
     * @param bool   $default
     * @return null|string
     */
    public function display(string $image, array $params = [], bool $default = false) : ?string
    {
        $cacheKey = $this->cacheKey($image, $params, $default);

        if ($this->hasCachedImage($cacheKey)) {
            return $this->getCachedImage($cacheKey);
        }

        $theme = $default ? $this->getDefaultTheme() : $this->getTheme();

        if (!$this->exists($image, $theme)) {
            if (!$default && $this->themeIsDefault($theme)) {
                return $this->display($image, $params, true);
            }

            $this->missing($image, $theme);

            return null;
        }

        return $this->cacheImage($cacheKey, $this->createHtmlImageString($image, $theme, array_merge([
            'alt'   => $image,
        ], $params)));
    }

    /**
     * Display alias
     *
     * @param string $image
     * @param array  $params
     * @param bool   $default
     * @return null|string
     */
    public function show(string $image, array $params = [], bool $default = false) : ?string
    {
        return $this->display($image, $params, $default);
    }

    /**
     * Create HTML image string
     *
     * @param string                $image
     * @param \Laranix\Themer\Theme $theme
     * @param array                 $params
     * @return string
     */
    protected function createHtmlImageString(string $image, Theme $theme, array $params = [])
    {
        if (!empty($params)) {
            $extra = [];

            foreach ($params as $key => $param) {
                $extra[] = $key . '="' . $param . '"';
            }

            $properties = implode(' ', $extra) . ' ';
        }

        return sprintf('<img src="%s" %s/>', $this->getWebUrl($image, $theme), $properties ?? '');
    }

     /**
     * Check for cached image
     *
     * @param string $key
     * @return bool
     */
    protected function hasCachedImage(string $key) : bool
    {
        return $this->resources->has($key);
    }

    /**
     * Get cached image
     *
     * @param string $key
     * @return string
     */
    protected function getCachedImage(string $key) : string
    {
        return $this->resources->get($key);
    }

    /**
     * Store image in the cache
     *
     * @param string $key
     * @param string $image
     * @return string
     */
    protected function cacheImage($key, string $image) : string
    {
        $this->resources->add($key, $image);

        return $image;
    }

    /**
     * Cached image key
     *
     * @param string $image
     * @param array  $params
     * @param bool   $default
     * @return string
     */
    protected function cacheKey(string $image, array $params = [], bool $default = false) : string
    {
        return hash('crc32', json_encode([$image, $params, $default]));
    }

    /**
     * Add and track a new resource
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    public function add($settings)
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Output the processed resources
     *
     * @param array|null  $options
     * @param string|null $view
     * @return null|string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    public function output(?array $options = [], string $view = null) : ?string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getRemoteResourceRepositoryKey(ResourceSettings $settings): string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Get repository key for local resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getLocalResourceRepositoryKey(ResourceSettings $settings): string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getCompiledLocalResourceRepositoryKey(ResourceSettings $settings): string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Process resources
     *
     * @param array $options
     * @return array|null
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getResourcePayload(array $options = []): ?array
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Group resources together by type
     *
     * @param array $compiled
     * @return array|null
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function parseLocalResources(?array $compiled): ?array
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Creates output for resources
     *
     * @param array|null $resources
     * @return string|null
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function createResourceOutput(?array $resources) : ?string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Create and return settings for resource.
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $type
     * @param string                $resource
     * @return \Laranix\Themer\ResourceSettings
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function createLocalResourceFileSettings(Theme $theme, string $type, string $resource): ResourceSettings
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Set the subdirectory in the theme for the resource type
     *
     * @return string
     */
    protected function getDirectory(): string
    {
        return 'images';
    }

    /**
     * Set config key in themer config
     *
     * @return string
     */
    protected function getConfigKey(): string
    {
        return 'image';
    }

    /**
     * Set settings class name
     *
     * @return string|null
     */
    protected function getSettingsClass(): ?string
    {
        return null;
    }
}
