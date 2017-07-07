<?php
namespace Laranix\Themer\Style;

use Laranix\Themer\FileSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerFile;

class Style extends ThemerFile
{
//    /**
//     * @var array
//     */
//    protected $devices = ['all', 'print', 'screen', 'speech'];

    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings|Settings $settings
     * @return string
     */
    protected function getRemoteRepositoryKey(FileSettings $settings) : string
    {
        return sprintf('style.remote.%d', $settings->order);
    }

    /**
     * Get repository key for local files
     *
     * @param \Laranix\Themer\FileSettings|Settings $settings
     * @return string
     */
    protected function getLocalRepositoryKey(FileSettings $settings) : string
    {
        return sprintf('style.local.%s.%s.%d', $settings->theme->getKey(), $this->crcCache($settings->media), $settings->order);
    }

    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings|Settings $settings
     * @return string
     */
    protected function getCompiledLocalRepositoryKey(FileSettings $settings) : string
    {
        return sprintf('compiled.%s.%s', $settings->theme->getKey(), $this->crc($settings->media));
    }

    /**
     * Process files
     *
     * @param array $options
     * @return array|null
     */
    protected function getFilePayload(array $options = []) : ?array
    {
        $local  = $this->parseFiles($this->files->get('compiled'));
        $remote = $this->files->get('style.remote');

        $sheets = [
            'stylesheets'    => $this->mergeFileArrays($local, $remote)
        ];

        if ($sheets['stylesheets'] === null) {
            return null;
        }

        ksort($sheets['stylesheets']);

        return $sheets;
    }

    /**
     * Group files together by type
     *
     * @param array $compiled
     * @return array|null
     */
    protected function parseFiles(?array $compiled) : ?array
    {
        if ($compiled === null) {
            return null;
        }

        $stylesheets = [];

        foreach ($compiled as $themeName => $mediaGroup) {
            $theme = $this->getTheme($themeName);

            foreach ($mediaGroup as $media => $sheet) {
                $compiledFile       = sprintf('compiled_%s.css', $this->crc($media . $sheet));
                $compiledFilePath   = $this->getFilePath($compiledFile, $theme);

                if (!is_file($compiledFilePath)) {
                    $this->mergeFiles($theme, sprintf('style.local.%s.%s', $theme->getKey(), $media), $compiledFilePath);
                }

                $stylesheets[] = $this->createFileSettings($theme, $media, $compiledFile);
            }
        }

        return $stylesheets;
    }

    /**
     * Create and return settings for file.
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $type
     * @param string                $file
     * @return \Laranix\Themer\FileSettings|\Laranix\Themer\Style\Settings
     */
    protected function createFileSettings(Theme $theme, string $type, string $file): FileSettings
    {
        return new Settings([
            'key'   => $this->crc(sprintf('%s%s%s', $theme->getKey(), $type, $file)),
            'file'  => $file,
            'url'   => $this->getBaseUrl($theme),
            'theme' => $theme,
            'order' => ++$this->order,
            'media' => $this->getCrcValue($type),
        ]);
    }

    /**
     * Set the subdirectory in the theme for the file type
     *
     * @return string
     */
    protected function getDirectory(): string
    {
        return 'style';
    }

    /**
     * Set config key in themer config
     *
     * @return string
     */
    protected function getConfigKey(): string
    {
        return 'style';
    }
}
