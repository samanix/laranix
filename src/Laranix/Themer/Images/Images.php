<?php
namespace Laranix\Themer\Images;

use Laranix\Support\Exception\NotImplementedException;
use Laranix\Support\IO\Str\Str;
use Laranix\Themer\ResourceSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerResource;

class Images extends ThemerResource
{
    /**
     * Display an image
     *
     * @param string|Settings|array $image
     * @param null|string           $alt
     * @param array                 $extra
     * @param bool                  $default
     * @return null|string
     * @internal param array $params
     */
    public function display($image, ?string $alt = null, array $extra = [], bool $default = false) : ?string
    {
        $image = $this->createImageSettings($image, $alt, $extra, $default);

        if ($this->hasCachedImage($image)) {
            return $this->getCachedImage($image);
        }

        $image->hasRequiredSettings();

        if (!$this->exists($image->image, $image->theme)) {
            if (!$default && $this->themeIsDefault($image->theme)) {
                return $this->display($image, $alt, $extra, true);
            }

            $this->missing($image->image, $image->theme);

            // Null returned
            return $this->cacheImage($image);
        }

        return $this->cacheImage($this->generateHtmlOutput($image));
    }

    /**
     * Display alias
     *
     * @param string      $image
     * @param null|string $alt
     * @param array       $extra
     * @param bool        $default
     * @return null|string
     */
    public function show($image, ?string $alt = null, array $extra = [], bool $default = false) : ?string
    {
        return $this->display($image, $alt, $extra, $default);
    }

    /**
     * Check for cached image
     *
     * @param \Laranix\Themer\ResourceSettings $image
     * @return bool
     */
    protected function hasCachedImage(ResourceSettings $image) : bool
    {
        return $this->resources->has($image->key);
    }

    /**
     * Get cached image
     *
     * @param \Laranix\Themer\ResourceSettings $image
     * @return string
     */
    protected function getCachedImage(ResourceSettings $image) : string
    {
        return $this->resources->get($image->key)->htmlstring;
    }

    /**
     * Store image in the cache
     *
     * @param ResourceSettings|Settings    $image
     * @return string|null
     */
    protected function cacheImage(ResourceSettings $image) : ?string
    {
        $this->resources->add($image->key, $image);

        return $image->htmlstring;
    }

    /**
     * Cached image key
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     */
    protected function cacheKey(ResourceSettings $settings) : string
    {
        return hash('crc32', json_encode($settings));
    }

    /**
     * @param             $image
     * @param null|string $alt
     * @param array       $extra
     * @param bool        $default
     * @return \Laranix\Themer\ResourceSettings|Settings
     */
    protected function createImageSettings($image, ?string $alt = null, array $extra = [], bool $default = false) : ResourceSettings
    {
        if ($image instanceof Settings) {
            $settings = $image;
        } elseif (is_array($image)) {
            if (!empty($extra)) {
                $image['extra'] = array_merge($image['extra'] ?? [], $extra);
            }

            $settings = new Settings($image);
        } else {
            $settings = new Settings([
                'image'     => $image,
                'alt'       => $alt ?? $image,
                'extra'     => $extra,
            ]);
        }

        $settings->theme    = $default ? $this->getDefaultTheme() : $this->getTheme();
        $settings->default  = $default;
        $settings->key      = $this->cacheKey($settings);

        return $settings;
    }

    /**
     * Images output string
     *
     * @param \Laranix\Themer\ResourceSettings|Settings $image
     * @return \Laranix\Themer\ResourceSettings
     */
    protected function generateHtmlOutput(ResourceSettings $image) : ResourceSettings
    {
        $str = /** @lang text */
                '<img src="{{url}}" alt="{{alt}}" {{title}} {{width}} {{height}} {{class}} {{id}} {{crossorigin}} {{extra}} />';

        $image->htmlstring = Str::format($str, [
            'url'           => $this->getWebUrl($image->image, $image->theme),
            'alt'           => $image->alt,
            'title'         => $image->title !== null ? 'title="' . $image->title . '"' : null,
            'width'         => $image->width !== null ? 'width="' . $image->width . '"' : null,
            'height'        => $image->height !== null ? 'height="' . $image->height . '"' : null,
            'class'         => $image->class !== null ? 'class="' . $image->class . '"' : null,
            'id'            => $image->id !== null ? 'id="' . $image->id . '"' : null,
            'crossorigin'   => $image->crossorigin !== null ? ' crossorigin="' . $image->crossorigin . '"' : null,
            'extra'         => $this->createResourceOutput($image->extra),
        ]);

        return $image;
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
        if (empty($resources)) {
            return null;
        }

        $extra = [];

        foreach ($resources as $attr => $value) {
            $extra[] = $attr . '="' . $value . '"';
        }

        return implode(' ', $extra);
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
     * Set settings class name
     *
     * @return string|null
     */
    protected function getSettingsClass(): ?string
    {
        return null;
    }
}
