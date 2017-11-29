<?php
namespace Laranix\Themer\Images;

use Laranix\Support\Exception\NotImplementedException;
use Laranix\Support\IO\Str\Str;
use Laranix\Support\IO\Url\UrlSettings;
use Laranix\Themer\ResourceSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerResource;

class Images extends ThemerResource
{
    /**
     * Display an image
     *
     * @param string|LocalSettings|array $image
     * @param null|string                $alt
     * @param array                      $extra
     * @param bool                       $default
     * @return null|string
     * @internal param array $params
     */
    public function display($image, ?string $alt = null, array $extra = [], bool $default = false): ?string
    {
        return $this->generateImagePayload($image, $alt, $extra, $default)->htmlstring;
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
     * Get url for image
     *
     * @param $image
     * @param $alt
     * @param $extra
     * @param $default
     * @return null|string
     */
    public function url($image, ?string $alt = null, array $extra = [], bool $default = false): ?string
    {
        return $this->generateImagePayload($image, $alt, $extra, $default)->url;
    }

    /**
     * Create the image payload
     *
     * @param $image
     * @param $alt
     * @param $extra
     * @param $default
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Images\LocalSettings
     */
    protected function generateImagePayload($image, ?string $alt, array $extra, bool $default): ResourceSettings
    {
        $image = $this->createImageSettings($image, $alt, $extra, $default);

        if ($this->hasCachedImage($image)) {
            return $this->getCachedImage($image);
        }

        $image->hasRequiredSettings();

        if (!$image instanceof RemoteSettings && !$this->exists($image->image, $image->theme)) {
            if (!$default && $this->themeIsDefault($image->theme)) {
                return $this->generateImagePayload($image, $alt, $extra, true);
            }

            $this->missing($image->image, $image->theme);

            // Null returned
            return $this->cacheImage($image);
        }

        return $this->cacheImage($image);
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
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Images\LocalSettings
     */
    protected function getCachedImage(ResourceSettings $image): ResourceSettings
    {
        return $this->resources->get($image->key);
    }

    /**
     * Store image in the cache
     *
     * @param ResourceSettings|LocalSettings $image
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Images\LocalSettings
     */
    protected function cacheImage(ResourceSettings $image): ResourceSettings
    {
        $this->resources->add($image->key, $image);

        return $image;
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
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Images\LocalSettings|\Laranix\Themer\Images\RemoteSettings
     */
    protected function createImageSettings($image, ?string $alt, array $extra, bool $default): ResourceSettings
    {
        // Remote image
        if (filter_var($image, FILTER_VALIDATE_URL) ||
            $image instanceof UrlSettings ||
            $image instanceof RemoteSettings) {
            $settings = $this->getRemoteImageSettings($image, $alt, $extra, $default);
        } else {
            $settings = $this->getLocalImageSettings($image, $alt, $extra, $default);
        }

        $settings->htmlstring   = $this->generateHtmlOutput($settings);
        $settings->key          = $this->cacheKey($settings);

        return $settings;
    }

    /**
     * Images output string
     *
     * @param \Laranix\Themer\ResourceSettings|LocalSettings $image
     * @return string
     */
    protected function generateHtmlOutput(ResourceSettings $image): string
    {
        $str = <<<'IMAGESTR'
<img src="{{url}}" alt="{{alt}}" {{title}} {{width}} {{height}} {{class}} {{id}} {{cors}} {{extra}} />
IMAGESTR;

        return Str::format($str, [
            'url'       => $image->url,
            'alt'       => e($image->alt),
            'title'     => $image->title !== null ? 'title="' . e($image->title) . '"' : null,
            'width'     => $image->width !== null ? 'width="' . e($image->width) . '"' : null,
            'height'    => $image->height !== null ? 'height="' . e($image->height) . '"' : null,
            'class'     => $image->class !== null ? 'class="' . e($image->class) . '"' : null,
            'id'        => $image->id !== null ? 'id="' . e($image->id) . '"' : null,
            'cors'      => $image->crossorigin !== null ? 'crossorigin="' . e($image->crossorigin) . '"' : null,
            'extra'     => $this->createResourceOutput($image->extra),
        ]);
    }

    /**
     * Creates output for resources
     *
     * @param array|null $resources
     * @return string|null
     */
    protected function createResourceOutput(?array $resources) : ?string
    {
        if (empty($resources)) {
            return null;
        }

        $extra = [];

        foreach ($resources as $attr => $value) {
            $extra[] = e($attr) . '="' . e($value) . '"';
        }

        return implode(' ', $extra);
    }

    /**
     * Create settings for local image
     *
     * @param             $image
     * @param null|string $alt
     * @param array       $extra
     * @param bool        $default
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Images\LocalSettings
     */
    protected function getLocalImageSettings($image, ?string $alt, array $extra, bool $default): ResourceSettings
    {
        if ($image instanceof LocalSettings) {
            $settings = $image;
        } elseif (is_array($image)) {
            if (!empty($extra)) {
                $image['extra'] = array_merge($image['extra'] ?? [], $extra);
            }

            $settings = new LocalSettings($image);
        } else {
            $settings = new LocalSettings([
                'image' => $image,
                'alt'   => $alt ?? $image,
                'extra' => $extra,
            ]);
        }

        $settings->theme        = $default ? $this->getDefaultTheme() : $this->getTheme();
        $settings->default      = $default;
        $settings->url          = $this->getWebUrl($settings->image, $settings->theme);

        return $settings;
    }

    /**
     * @param             $image
     * @param null|string $alt
     * @param array       $extra
     * @param bool        $default
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Images\RemoteSettings
     */
    protected function getRemoteImageSettings($image, ?string $alt, array $extra, bool $default): ResourceSettings
    {
        if ($image instanceof RemoteSettings) {
            return $image;
        }

        return new RemoteSettings([
            'theme'   => $default ? $this->getDefaultTheme() : $this->getTheme(),
            'default' => $default,
            'url'     => $this->url->url($image),
            'alt'     => $alt ?? '',
            'extra'   => $extra,
        ]);
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
     * @param string                $filename
     * @return \Laranix\Themer\ResourceSettings
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function createLocalResourceFileSettings(Theme $theme, string $type, string $filename): ResourceSettings
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
