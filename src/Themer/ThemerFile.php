<?php
namespace Laranix\Themer;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Logging\Log as Logger;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Laranix\Support\IO\Path;
use Laranix\Support\IO\Url\Url;
use Laranix\Support\IO\Url\Settings;
use Laranix\Support\IO\Repository;


abstract class ThemerFile
{
    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     */
    abstract protected function getRemoteRepositoryKey(FileSettings $settings) : string;

    /**
     * Get repository key for local files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     */
    abstract protected function getLocalRepositoryKey(FileSettings $settings) : string;

    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     */
    abstract protected function getCompiledLocalRepositoryKey(FileSettings $settings) : string;

    /**
     * Process files
     *
     * @param array $options
     * @return array|null
     */
    abstract protected function getFilePayload(array $options = []) : ?array;

    /**
     * Group files together by type
     *
     * @param array $compiled
     * @return array|null
     */
    abstract protected function parseFiles(?array $compiled) : ?array;

    /**
     * Create and return settings for file.
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $type
     * @param string                $file
     * @return \Laranix\Themer\FileSettings
     */
    abstract protected function createFileSettings(Theme $theme, string $type, string $file) : FileSettings;

    /**
     * Set the subdirectory in the theme for the file type
     *
     * @return string
     */
    abstract protected function getDirectory() : string;

    /**
     * Set config key in themer config
     *
     * @return string
     */
    abstract protected function getConfigKey() : string;

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
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $viewFactory;

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
     * Config key in themer.php
     *
     * @var string
     */
    protected $configKey;

    /**
     * File load order
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
     * File repository
     *
     * @var \Laranix\Support\IO\Repository
     */
    public $files;

    /**
     * ThemerFile constructor.
     *
     * @param \Laranix\Themer\Themer                  $themer
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Logging\Log       $logger
     * @param \Illuminate\Contracts\View\Factory      $viewFactory
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function __construct(Themer $themer, Config $config, Logger $logger, ViewFactory $viewFactory)
    {
        $this->themer       = $themer;
        $this->config       = $config;
        $this->logger       = $logger;
        $this->viewFactory  = $viewFactory;
        $this->files        = new Repository();

        $this->directory    = $this->getDirectory();
        $this->configKey    = $this->getConfigKey();
    }

    /**
     * Add and track a new file
     *
     * @param \Laranix\Themer\FileSettings $settings
     */
    public function add(FileSettings $settings)
    {
        $settings->theme = $this->getThemeToUse($settings);

        $addKey = sprintf('_added.%s.%s', $settings->theme->getName(), $settings->key);

        if ($this->files->has($addKey)) {
            $this->logger->warning("Key already exists in Themer: '{$settings->key}'");
            return;
        }

        if ($this->addResource($settings)) {
            $this->files->add($addKey, true);

            ++$this->order;
        }
    }

    /**
     * Add a resource
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return bool
     */
    protected function addResource(FileSettings $settings)
    {
        $settings = $this->prepareFileSettings($settings);

        if ($settings === null) {
            return false;
        }

        $settings->hasRequired();

        return $settings->url === null ? $this->addLocalResource($settings) : $this->addRemoteResource($settings);
    }

    /**
     * Prepare settings
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return \Laranix\Themer\FileSettings|null
     */
    protected function prepareFileSettings(FileSettings $settings) : ?FileSettings
    {
        if ($settings->order === -1) {
            $settings->order = $this->order;
        }

        if ($settings->url !== null) {
            return $settings;
        }

        $settings->filePath = $this->getFilePath($settings->file, $settings->theme);
        $settings->exists   = is_file($settings->filePath);

        if ($settings->automin) {
            $settings->file = $this->searchForMinified($settings);
        }

        if (!$settings->exists) {
            if ($settings->defaultFallback && !$this->themeIsDefault($settings->theme)) {
                $settings->theme = $this->getDefaultTheme();

                return $this->prepareFileSettings($settings);
            }

            $this->missing($settings->file, $settings->theme);

            return null;
        }

        $settings->mtime = filemtime($this->getFilePath($settings->file, $settings->theme));

        return $settings;
    }

    /**
     * Add remote resource
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return bool
     */
    protected function addRemoteResource(FileSettings $settings): bool
    {
        $settings->repositoryKey = $this->getRemoteRepositoryKey($settings);

        if ($this->files->has($settings->repositoryKey)) {
            ++$settings->order;

            return $this->addRemoteResource($settings);
        }

        $this->files->add($settings->repositoryKey, $settings);

        return true;
    }

    /**
     * Add local resource
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return bool
     */
    protected function addLocalResource(FileSettings $settings): bool
    {
        $settings->repositoryKey = $this->getLocalRepositoryKey($settings);

        if ($this->files->has($settings->repositoryKey)) {
            ++$settings->order;

            return $this->addLocalResource($settings);
        }

        $this->files->add($settings->repositoryKey, $settings);

        $compileKey = $this->getCompiledLocalRepositoryKey($settings);

        if (!$this->files->has($compileKey)) {
            $this->files->add($compileKey, '');
        }

        $this->files[$compileKey] .= $this->crc($settings->file . $settings->theme->getKey() . $settings->mtime);

        return true;
    }

    /**
     * Search for minified version of file
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @param string                       $min
     * @return string
     */
    protected function searchForMinified(FileSettings $settings, string $min = 'min') : string
    {
        if (strpos($settings->file, ".{$min}.") !== false) {
            return $settings->file;
        }

        $pathinfo = pathinfo($settings->filePath);

        $minFile = "{$pathinfo['filename']}.{$min}.{$pathinfo['extension']}";

        return $this->exists($minFile, $settings->theme) ? $minFile : $settings->file;
    }

    /**
     * Render the view
     *
     * @param array|null  $options
     * @param string|null $view
     * @return null|string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function render(?array $options = [], string $view = null) : ?string
    {
        $view = $view ?? $this->config->get("themer.views.{$this->configKey}", "layout.themer.{$this->configKey}");

        if ($this->viewFactory->exists($view)) {
            $data = $this->getFilePayload($options);

            if (empty($data) || $data === null) {
                return null;
            }

            return $this->viewFactory->make($view, $data)->render();
        }

        throw new FileNotFoundException("View '{$view}' not found");
    }

    /**
     * Check if file exists
     *
     * @param string                $file
     * @param \Laranix\Themer\Theme $theme
     * @return bool
     */
    protected function exists(string $file, Theme $theme = null) : bool
    {
        return is_file($this->getFilePath($file, $theme));
    }

    /**
     * Get path to a file
     *
     * @param string                $file
     * @param \Laranix\Themer\Theme $theme
     * @return string
     */
    public function getFilePath(string $file, Theme $theme = null) : string
    {
        return $this->createPath($this->getBasePath($theme), $file);
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
     * Get URL to web file
     *
     * @param string                $file
     * @param \Laranix\Themer\Theme $theme
     * @return string
     */
    public function getWebUrl(string $file, Theme $theme = null) : string
    {
        return $this->createPath($this->getBaseUrl($theme), $file);
    }

    /**
     * Get web path for resource group
     *
     * @param \Laranix\Themer\Theme $theme
     * @return string
     */
    protected function getBaseUrl(Theme $theme = null) : string
    {
        $theme = $theme ?? $this->getTheme();

        if (isset($this->webPaths[$theme->getKey()])) {
            return $this->webPaths[$theme->getKey()];
        }

        return $this->webPaths[$theme->getKey()] = Url::url(new Settings([
            'domain'        => $theme->getWebPath(),
            'path'          => $this->directory,
            'trailingSlash' => true,
        ]));
    }

    /**
     * Log missing file
     *
     * @param string                $file
     * @param \Laranix\Themer\Theme $theme
     */
    protected function missing(string $file, Theme $theme = null)
    {
        $path = $this->getBasePath();

        $message = "{$file} not found in {$path}";

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
     * @param \Laranix\Themer\FileSettings $settings
     * @return \Laranix\Themer\Theme
     */
    protected function getThemeToUse(FileSettings $settings) : Theme
    {
        if ($settings->theme instanceof Theme) {
            return $settings->theme;
        }

        return $settings->themeName !== null ? $this->getTheme($settings->themeName) : $this->getDefaultTheme();
    }

    /**
     * Merge files
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $key
     * @param string                $compileFileName
     */
    protected function mergeFiles(Theme $theme, string $key, string $compileFileName)
    {
        $files = $this->files->get($key);

        if ($files === null) {
            return;
        }

        ksort($files);

        ob_start();

        foreach ($files as $file) {
            include $this->getFilePath($file->file, $theme);
        }

        $compileFile = fopen($compileFileName, 'w');
        fwrite($compileFile, ob_get_contents());
        fclose($compileFile);

        ob_end_clean();
    }

    /**
     * Merge arrays to one
     *
     * @param array ...$arrays
     * @return array|null
     */
    protected function mergeFileArrays(...$arrays) : ?array
    {
        $merged = [];

        foreach ($arrays as $array) {
            if (!is_array($array)) {
                continue;
            }

            /** @var FileSettings $file */
            foreach ($array as $file) {
                $this->mergeWithOrder($file, $merged);
            }
        }

        return !empty($merged) ? $merged : null;
    }

    /**
     * Merge files to one array preserving order
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
