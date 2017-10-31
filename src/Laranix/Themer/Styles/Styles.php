<?php
namespace Laranix\Themer\Styles;

use Laranix\Support\IO\Str\Str;
use Laranix\Themer\ResourceSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerResource;

class Styles extends ThemerResource
{
    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings|Settings $settings
     * @return string
     */
    protected function getRemoteResourceRepositoryKey(ResourceSettings $settings) : string
    {
        return sprintf('styles.remote.%d', $settings->order);
    }

    /**
     * Get repository key for local resources
     *
     * @param \Laranix\Themer\ResourceSettings|Settings $settings
     * @return string
     */
    protected function getLocalResourceRepositoryKey(ResourceSettings $settings) : string
    {
        return sprintf(
            'styles.local.%s.%s.%d',
            $settings->theme->getKey(),
            $this->crcCache($settings->media),
            $settings->order
        );
    }

    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings|Settings $settings
     * @return string
     */
    protected function getCompiledLocalResourceRepositoryKey(ResourceSettings $settings) : string
    {
        return sprintf('compiled.%s.%s', $settings->theme->getKey(), $this->crc($settings->media));
    }

    /**
     * Process resources
     *
     * @param array $options
     * @return array|null
     */
    protected function getResourcePayload(array $options = []) : ?array
    {
        $local  = $this->parseLocalResources($this->resources->get('compiled'));
        $remote = $this->resources->get('styles.remote');

        $sheets = $this->mergeResourceArrays($local, $remote);

        if ($sheets === null) {
            return null;
        }

        ksort($sheets);

        return $sheets;
    }

    /**
     * Group resources together by type
     *
     * @param array $compiled
     * @return array|null
     */
    protected function parseLocalResources(?array $compiled) : ?array
    {
        if ($compiled === null) {
            return null;
        }

        $stylesheets = [];

        foreach ($compiled as $themeName => $mediaGroup) {
            $theme = $this->getTheme($themeName);

            foreach ($mediaGroup as $media => $sheet) {
                $compiledResources          = sprintf('compiled_%s.css', $this->crc($media . $sheet));
                $compiledResourceFilePath   = $this->getResourcePath($compiledResources, $theme);

                if (!is_file($compiledResourceFilePath)) {
                    $this->mergeResources(
                        sprintf('styles.local.%s.%s', $theme->getKey(), $media),
                        $compiledResourceFilePath
                    );
                }

                $stylesheets[] = $this->createLocalResourceFileSettings($theme, $media, $compiledResources);
            }
        }

        return $stylesheets;
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

        $output = [];

        foreach ($resources as $resource) {
            $str = <<<'STYLESTR'
<link rel="stylesheet" type="text/css" href="{{url}}" media="{{media}}" {{integ}} {{cors}} />
STYLESTR;

            $output[] = Str::format($str, [
                'url'   => $this->url->create(null, $resource->url, $resource->filename),
                'media' => $resource->media,
                'integ' => $resource->integrity !== null ? 'integrity="' . $resource->integrity . '"' : null,
                'cors'  => $resource->crossorigin !== null ? 'crossorigin="' . $resource->crossorigin . '"' : null,
            ]);
        }

        return implode("\n", $output);
    }

    /**
     * Create and return settings for resource.
     *
     * @param \Laranix\Themer\Theme $theme
     * @param string                $type
     * @param string                $resource
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Styles\Settings
     */
    protected function createLocalResourceFileSettings(Theme $theme, string $type, string $resource): ResourceSettings
    {
        return new Settings([
            'key'       => $this->crc(sprintf('%s%s%s', $theme->getKey(), $type, $resource)),
            'resource'  => $resource,
            'url'       => $this->getBaseUrl($theme),
            'theme'     => $theme,
            'order'     => ++$this->order,
            'media'     => $this->getCrcValue($type),
        ]);
    }

    /**
     * Set the subdirectory in the theme for the resource type
     *
     * @return string
     */
    protected function getDirectory(): string
    {
        return 'styles';
    }

    /**
     * Set settings class name
     *
     * @return string|null
     */
    protected function getSettingsClass(): ?string
    {
        return Settings::class;
    }
}
