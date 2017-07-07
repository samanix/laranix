<?php
namespace Laranix\Themer\Script;

use Laranix\Themer\FileSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerFile;

class Script extends ThemerFile
{
    /**
     * Processed scripts, stored so as not to double compile in head/body
     *
     * @var array
     */
    protected $processedScripts;

    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings|Settings $settings
     * @return string
     */
    protected function getRemoteRepositoryKey(FileSettings $settings) : string
    {
        return sprintf('scripts.remote.%s.%d', $this->getLocation($settings->head), $settings->order);
    }

    /**
     * Get repository key for local files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     */
    protected function getLocalRepositoryKey(FileSettings $settings) : string
    {
        return sprintf('scripts.local.%s.%s.%d', $settings->theme->getKey(), $this->getAttributes($settings), $settings->order);
    }

    /**
     * Get repository key for remote files
     *
     * @param \Laranix\Themer\FileSettings $settings
     * @return string
     */
    protected function getCompiledLocalRepositoryKey(FileSettings $settings) : string
    {
        return sprintf('compiled.%s.%s', $settings->theme->getKey(), $this->getAttributes($settings));
    }

    /**
     * Process files
     *
     * @param array $options
     * @return array|null
     */
    protected function getFilePayload(array $options = []) : ?array
    {
        $location = $this->getLocation($options['head'] ?? true);

        if ($this->processedScripts === null) {
            $local  = $this->parseFiles($this->files->get('compiled'));
            $remote = $this->files->get('scripts.remote');

            $this->processedScripts = [
                'head' => [ 'scripts' => $this->mergeFileArrays($local['head'] ?? null, $remote['head'] ?? null) ],
                'body' => [ 'scripts' => $this->mergeFileArrays($local['body'] ?? null, $remote['body'] ?? null) ],
            ];

            if ($this->processedScripts['head']['scripts'] !== null) {
                ksort($this->processedScripts['head']['scripts']);
            }

            if ($this->processedScripts['body']['scripts'] !== null) {
                ksort($this->processedScripts['body']['scripts']);
            }
        }

        return $this->processedScripts[$location]['scripts'] !== null ? $this->processedScripts[$location] : null;
    }

    /**
     * Group files together by type
     *
     * @param array $compiled
     * @return array|null
     */
    protected function parseFiles(?array $compiled): ?array
    {
        if ($compiled === null) {
            return null;
        }

        $scripts = [
            'head'  => [],
            'body'  => [],
        ];

        foreach ($compiled as $themeName => $attr) {
            $theme = $this->getTheme($themeName);

            foreach ($attr as $type => $script) {
                $compiledFile       = sprintf('compiled_%s.js', $this->crc($type . $script));
                $compiledFilePath   = $this->getFilePath($compiledFile, $theme);

                if (!is_file($compiledFilePath)) {
                    $this->mergeFiles($theme, sprintf('scripts.local.%s.%s', $theme->getKey(), $type), $compiledFilePath);
                }

                $location = $this->getLocation(strpos($type, 'head') !== false);

                $scripts[$location][] = $this->createFileSettings($theme, $type, $compiledFile);

            }
        }

        return $scripts;
    }

    /**
     * Create and return settings for file.
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $type
     * @param string                $file
     * @return \Laranix\Themer\FileSettings|\Laranix\Themer\Script\Settings
     */
    protected function createFileSettings(Theme $theme, string $type, string $file): FileSettings
    {
        return new Settings([
            'key'   => $this->crc(sprintf('%s%s%s', $theme->getKey(), $type, $file)),
            'file'  => $file,
            'url'   => $this->getBaseUrl($theme),
            'theme' => $theme,
            'async' => strpos($type, 'async') !== false,
            'defer' => strpos($type, 'defer') !== false,
            'order' => ++$this->order,
        ]);
    }

    /**
     * Set the subdirectory in the theme for the file type
     *
     * @return string
     */
    protected function getDirectory(): string
    {
        return 'scripts';
    }

    /**
     * Set config key in themer config
     *
     * @return string
     */
    protected function getConfigKey(): string
    {
        return 'scripts';
    }

    /**
     * Get location for scripts
     *
     * @param bool $head
     * @return string
     */
    protected function getLocation(bool $head) : string
    {
        return $head ? 'head' : 'body';
    }

    /**
     * Get parameters for script (async, defer)
     *
     * @param \Laranix\Themer\FileSettings|Settings $settings
     * @return string
     */
    protected function getAttributes(FileSettings $settings) : string
    {
        $attributes = [
            $this->getLocation($settings->head),
        ];

        if ($settings->async) {
            $attributes[] = 'async';
        }

        if ($settings->defer) {
            $attributes[] = 'defer';
        }

        return implode('_', $attributes);
    }
}
