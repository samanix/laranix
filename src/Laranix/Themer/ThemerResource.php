<?php
namespace Laranix\Themer;

use Illuminate\Contracts\Logging\Log as Logger;
use Illuminate\Contracts\Config\Repository as Config;
use Laranix\Support\Exception\InvalidInstanceException;
use Laranix\Support\Exception\InvalidTypeException;
use Laranix\Support\IO\Path;
use Laranix\Support\IO\Url\Url;
use Laranix\Support\IO\Url\UrlSettings;
use Laranix\Support\IO\Repository;
use Laranix\Support\Settings;

abstract class ThemerResource
{
    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     */
    abstract protected function getRemoteResourceRepositoryKey(ResourceSettings $settings) : string;

    /**
     * Get repository key for local resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     */
    abstract protected function getLocalResourceRepositoryKey(ResourceSettings $settings) : string;

    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     */
    abstract protected function getCompiledLocalResourceRepositoryKey(ResourceSettings $settings) : string;

    /**
     * Process resources
     *
     * @param array $options
     * @return array|null
     */
    abstract protected function getResourcePayload(array $options = []) : ?array;

    /**
     * Group resources together by type
     *
     * @param array $compiled
     * @return array|null
     */
    abstract protected function parseLocalResources(?array $compiled) : ?array;

    /**
     * Creates output for resources
     *
     * @param array|null $resources
     * @return string|null
     */
    abstract protected function createResourceOutput(?array $resources) : ?string;

    /**
     * Create and return settings for resource.
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $type
     * @param string                $filename
     * @return \Laranix\Themer\ResourceSettings
     */
    abstract protected function createLocalResourceFileSettings(
        Theme $theme,
        string $type,
        string $filename
    ): ResourceSettings;

    /**
     * @var \Laranix\Themer\Themer
     */
    protected $themer;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Contracts\Logging\Log
     */
    protected $logger;

    /**
     * @var \Laranix\Support\IO\Url\Url
     */
    protected $url;

    /**
     * @var bool
     */
    protected $mergeResources = true;

    /**
     * @var array
     */
    protected $fullPaths = [];

    /**
     * @var array
     */
    protected $webPaths = [];

    /**
     * Subdirectory in the main theme path
     *
     * @var string
     */
    protected $directory;

    /**
     * Resource settings class name
     *
     * @var string
     */
    protected $settings;

    /**
     * Resource load order
     *
     * @var int
     */
    protected $order = 0;

    /**
     * Cache crc values
     *
     * @var array
     */
    protected $crcCache = [];

    /**
     * Resource repository
     *
     * @var \Laranix\Support\IO\Repository
     */
    public $resources;

    /**
     * ThemerResource constructor.
     *
     * @param \Laranix\Themer\Themer                  $themer
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Logging\Log       $logger
     * @param \Laranix\Support\IO\Url\Url             $url
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    public function __construct(Themer $themer, Config $config, Logger $logger, Url $url)
    {
        $this->themer   = $themer;
        $this->config   = $config;
        $this->logger   = $logger;
        $this->url      = $url;

        $this->resources      = new Repository();
        $this->mergeResources = !in_array(
            $this->config->get('app.env', 'production'),
            (array) $this->config->get('themer.ignored')
        );

        if (!is_a($this->settings, ResourceSettings::class, true)) {
            throw new InvalidTypeException('Settings property must be instance of ' . Settings::class);
        }

        if (!is_string($this->directory)) {
            throw new InvalidTypeException('Directory property must be a string');
        }
    }

    /**
     * Add and track a new resource
     *
     * @param \Laranix\Themer\ResourceSettings|array $settings
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     * @throws \Laranix\Support\Exception\LaranixSettingsException
     */
    public function add($settings)
    {
        if (is_array($settings)) {
            $settings = new $this->settings($settings);
        }

        if (!$settings instanceof ResourceSettings) {
            throw new InvalidInstanceException('Settings is not a valid instance of ' . ResourceSettings::class);
        }

        $settings->hasRequiredSettings();

        $settings->theme = $this->getThemeToUse($settings);

        $addKey = sprintf('_added.%s.%s', $settings->theme->getName(), $settings->key);

        if ($this->resources->has($addKey)) {
            $this->logger->warning("Key already exists in Themer: '{$settings->key}'");
            return;
        }

        if ($this->addResource($settings)) {
            $this->resources->add($addKey, true);

            ++$this->order;
        }
    }

    /**
     * Add a resource
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return bool
     */
    protected function addResource(ResourceSettings $settings)
    {
        $settings = $this->prepareResourceSettings($settings);

        if ($settings === null) {
            return false;
        }

        return $settings->url === null && $this->mergeResources && $settings->compile
            ? $this->addLocalResource($settings)
            : $this->addRemoteResource($settings);
    }

    /**
     * Prepare settings
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return \Laranix\Themer\ResourceSettings|null
     */
    protected function prepareResourceSettings(ResourceSettings $settings) : ?ResourceSettings
    {
        if ($settings->order === -1) {
            $settings->order = $this->order;
        }

        if ($settings->url !== null) {
            return $settings;
        }

        $settings->resourcePath = $this->getResourcePath($settings->filename, $settings->theme);
        $settings->exists       = is_file($settings->resourcePath);

        if ($settings->automin) {
            $settings->filename = $this->searchForMinifiedResource($settings);
        }

        if (!$settings->exists) {
            if ($settings->defaultFallback && !$this->themeIsDefault($settings->theme)) {
                $settings->theme = $this->getDefaultTheme();

                return $this->prepareResourceSettings($settings);
            }

            $this->missing($settings->filename, $settings->theme);

            return null;
        }

        $settings->mtime = filemtime($this->getResourcePath($settings->filename, $settings->theme));

        return $settings;
    }

    /**
     * Add remote resource
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return bool
     */
    protected function addRemoteResource(ResourceSettings $settings): bool
    {
        $settings->repositoryKey = $this->getRemoteResourceRepositoryKey($settings);

        if ($this->resources->has($settings->repositoryKey)) {
            ++$settings->order;

            return $this->addRemoteResource($settings);
        }

        if ($settings->url === null && (!$this->mergeResources || !$settings->compile)) {
            $settings->url = $this->getThemeBaseUrl($settings->theme);
        }

        $this->resources->add($settings->repositoryKey, $settings);

        return true;
    }

    /**
     * Add local resource
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return bool
     */
    protected function addLocalResource(ResourceSettings $settings): bool
    {
        $settings->repositoryKey = $this->getLocalResourceRepositoryKey($settings);

        if ($this->resources->has($settings->repositoryKey)) {
            ++$settings->order;

            return $this->addLocalResource($settings);
        }

        $this->resources->add($settings->repositoryKey, $settings);

        $compileKey = $this->getCompiledLocalResourceRepositoryKey($settings);

        if (!$this->resources->has($compileKey)) {
            $this->resources->add($compileKey, '');
        }

        $this->resources[$compileKey] .= $this->crc($settings->filename . $settings->theme->getKey() . $settings->mtime);

        return true;
    }

    /**
     * Search for minified version of resource
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @param string                           $min
     * @return string
     */
    protected function searchForMinifiedResource(ResourceSettings $settings, string $min = 'min') : string
    {
        if (strpos($settings->filename, ".{$min}.") !== false) {
            return $settings->filename;
        }

        $pathinfo = pathinfo($settings->resourcePath);

        $minResource = "{$pathinfo['filename']}.{$min}.{$pathinfo['extension']}";

        return $this->exists($minResource, $settings->theme) ? $minResource : $settings->filename;
    }

    /**
     * Output the processed resources
     *
     * @param array|null  $options
     * @return null|string
     */
    public function output(?array $options = []) : ?string
    {
        return $this->createResourceOutput($this->getResourcePayload($options));
    }

    /**
     * Check if resource exists
     *
     * @param string                $resource
     * @param \Laranix\Themer\Theme $theme
     * @return bool
     */
    protected function exists(string $resource, Theme $theme = null) : bool
    {
        return is_file($this->getResourcePath($resource, $theme));
    }

    /**
     * Get path to a resource
     *
     * @param string                $resource
     * @param \Laranix\Themer\Theme $theme
     * @return string
     */
    public function getResourcePath(string $resource, Theme $theme = null) : string
    {
        return $this->createPath($this->getBasePath($theme), $resource);
    }

    /**
     * Get full base path
     *
     * @param \Laranix\Themer\Theme $theme
     * @return string
     */
    protected function getBasePath(Theme $theme = null) : string
    {
        $theme = $theme ?? $this->getTheme();

        if (isset($this->fullPaths[$theme->getKey()])) {
            return $this->fullPaths[$theme->getKey()];
        }

        return $this->fullPaths[$theme->getKey()] = $this->createPath($theme->getPath(), $this->directory);
    }

    /**
     * Get a path to a resource
     *
     * @param \string[] $parts
     * @return string
     */
    protected function createPath(string ...$parts) : string
    {
        return call_user_func_array([Path::class, 'combine'], $parts);
    }

    /**
     * Get URL to web resource
     *
     * @param string                $resource
     * @param \Laranix\Themer\Theme $theme
     * @return string
     */
    public function getThemeResourceUrl(string $resource, Theme $theme = null) : string
    {
        return $this->createPath($this->getThemeBaseUrl($theme), $resource);
    }

    /**
     * Get web path for resource group
     *
     * @param \Laranix\Themer\Theme $theme
     * @return string
     */
    protected function getThemeBaseUrl(?Theme $theme = null) : string
    {
        $theme = $theme ?? $this->getTheme();

        if (isset($this->webPaths[$theme->getKey()])) {
            return $this->webPaths[$theme->getKey()];
        }

        return $this->webPaths[$theme->getKey()] = $this->url->make(new UrlSettings([
            'domain'        => $theme->getWebPath(),
            'path'          => $this->directory,
        ]));
    }

    /**
     * Get the url for output
     *
     * @param $url
     * @param $file
     * @return string
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function parseOutputUrl($url, $file): string
    {
        if (is_string($url)) {
            $url = rtrim($url, '/') . '/' . ltrim($file,  '/');

            return $this->url->url($url);
        }

        if (is_array($url)) {
            $url = new UrlSettings($url);
        }

        if (!($url instanceof UrlSettings)) {
            throw new InvalidInstanceException('Url is not a valid instance of UrlSettings');
        }

        if (is_string($url->path)) {
            $url->path = explode('/', $url->path);
        }

        $url->path[] = $file;

        return $this->url->url($url);
    }

    /**
     * Log missing resource
     *
     * @param string                $resource
     * @param \Laranix\Themer\Theme $theme
     */
    protected function missing(string $resource, Theme $theme = null)
    {
        $path = $this->getBasePath();

        $message = "{$resource} not found in {$path}";

        if (!$this->themeIsDefault($theme)) {
            $default = $this->getBasePath($this->getDefaultTheme());

            $message .= " or {$default}";
        }

        $this->logger->warning($message);
    }

    /**
     * Get theme
     *
     * @param string $theme
     * @return \Laranix\Themer\Theme
     */
    protected function getTheme(string $theme = null) : Theme
    {
        return $this->themer->getTheme($theme);
    }

    /**
     * Get default theme
     *
     * @return \Laranix\Themer\Theme
     */
    protected function getDefaultTheme() : Theme
    {
        return $this->themer->getDefaultTheme();
    }

    /**
     * Check if given theme is default
     *
     * @param \Laranix\Themer\Theme|null $theme
     * @return bool
     */
    protected function themeIsDefault(Theme $theme = null) : bool
    {
        return $this->themer->themeIsDefault($theme);
    }

    /**
     * Get theme to use
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return \Laranix\Themer\Theme
     */
    protected function getThemeToUse(ResourceSettings $settings) : Theme
    {
        if ($settings->theme instanceof Theme) {
            return $settings->theme;
        }

        return $settings->themeName !== null ? $this->getTheme($settings->themeName) : $this->getDefaultTheme();
    }

    /**
     * Merge resources
     *
     * @param string                $key
     * @param string                $compileResourceFileName
     */
    protected function mergeResources(string $key, string $compileResourceFileName)
    {
        $resources = $this->resources->get($key);

        if ($resources === null) {
            return;
        }

        ksort($resources);

        ob_start();

        foreach ($resources as $resource) {
            include $resource->resourcePath;
        }

        $compileResource = fopen($compileResourceFileName, 'w');
        fwrite($compileResource, ob_get_contents());
        fclose($compileResource);

        ob_end_clean();
    }

    /**
     * Merge arrays to one
     *
     * @param array ...$arrays
     * @return array|null
     */
    protected function mergeResourceArrays(...$arrays) : ?array
    {
        $merged = [];

        foreach ($arrays as $array) {
            if (!is_array($array)) {
                continue;
            }

            /** @var ResourceSettings $resource */
            foreach ($array as $resource) {
                $this->mergeWithOrder($resource, $merged);
            }
        }

        return !empty($merged) ? $merged : null;
    }

    /**
     * Merge resources to one array preserving order
     *
     * @param $settings
     * @param $merge
     * @return mixed
     */
    protected function mergeWithOrder($settings, &$merge)
    {
        if (isset($merge[$settings->order])) {
            $this->mergeWithOrder(++$settings->order, $merge);
        }

        return $merge[$settings->order] = $settings;
    }

    /**
     * CRC a string
     *
     * @param string $value
     * @param bool   $cache
     * @return string
     */
    protected function crc(string $value, bool $cache = false) : string
    {
        $hash = hash('crc32', $value);

        if ($cache && !isset($this->crcCache[$hash])) {
            $this->crcCache[$hash] = $value;
        }

        return $hash;
    }

    /**
     * CRC value and cache
     *
     * @param string $value
     * @return string
     */
    protected function crcCache(string $value) : string
    {
        return $this->crc($value, true);
    }

    /**
     * Get CRC string value
     *
     * @param string $crc
     * @return null|string
     */
    protected function getCrcValue(string $crc) : ?string
    {
        return isset($this->crcCache[$crc]) ? $this->crcCache[$crc] : null;
    }
}
