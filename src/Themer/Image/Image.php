<?php
namespace Laranix\Themer\Image;

use Laranix\Support\Exception\NotImplementedException;
use Laranix\Themer\FileSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerFile;

class Image extends ThemerFile
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
        if ($this->files->has($image)) {
            return $this->files->get($image);
        }

        $theme = $default ? $this->getDefaultTheme() : $this->getTheme();

        if (!$this->exists($image, $theme)) {
            if (!$default && $this->themeIsDefault($theme)) {
                return $this->display($image, $params, true);
            }

            $this->missing($image, $theme);

            return null;
        }

        $img = '<img src="' . $this->getWebUrl($image, $theme) . '" ';

        foreach ($params as $key => $param) {
            $img .= $key . '="' . $param . '" ';
        }

        $img .= '/>';

        return $this->files[$image] = $img;
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
     * Add and track a new file
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    public function add(?FileSettings $settings = null)
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Render the view
     *
     * @param array|null  $options
     * @param string|null $view
     * @return null|string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    public function render(?array $options = [], string $view = null) : ?string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getRemoteRepositoryKey(FileSettings $settings): string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Get repository key for local files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getLocalRepositoryKey(FileSettings $settings): string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getCompiledLocalRepositoryKey(FileSettings $settings): string
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Process files
     *
     * @param array $options
     * @return array|null
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function getFilePayload(array $options = []): ?array
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Group files together by type
     *
     * @param array $files
     * @return array|null
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function parseFiles(?array $files): ?array
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Create and return settings for file.
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $type
     * @param string                $file
     * @return \Laranix\Themer\FileSettings
     * @throws \Laranix\Support\Exception\NotImplementedException
     */
    protected function createFileSettings(Theme $theme, string $type, string $file): FileSettings
    {
        throw new NotImplementedException('Method not required for ' . get_class($this));
    }

    /**
     * Set the subdirectory in the theme for the file type
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
}
