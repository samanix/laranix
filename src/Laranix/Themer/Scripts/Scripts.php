<?php
namespace Laranix\Themer\Scripts;

use Laranix\Support\IO\Str\Str;
use Laranix\Themer\ResourceSettings;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemerResource;

class Scripts extends ThemerResource
{
    /**
     * Processed scripts, stored so as not to double compile in head/body
     *
     * @var array
     */
    protected $processedScripts;

    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings|Settings $settings
     * @return string
     */
    protected function getRemoteResourceRepositoryKey(ResourceSettings $settings) : string
    {
        return sprintf('scripts.remote.%s.%d', $this->getLocation($settings->head), $settings->order);
    }

    /**
     * Get repository key for local resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     */
    protected function getLocalResourceRepositoryKey(ResourceSettings $settings) : string
    {
        return sprintf(
            'scripts.local.%s.%s.%d',
            $settings->theme->getKey(),
            $this->getAttributes($settings),
            $settings->order
        );
    }

    /**
     * Get repository key for remote resources
     *
     * @param \Laranix\Themer\ResourceSettings $settings
     * @return string
     */
    protected function getCompiledLocalResourceRepositoryKey(ResourceSettings $settings) : string
    {
        return sprintf('compiled.%s.%s', $settings->theme->getKey(), $this->getAttributes($settings));
    }

    /**
     * Process resources
     *
     * @param array $options
     * @return array|null
     */
    protected function getResourcePayload(array $options = []) : ?array
    {
        $location = $this->getLocation($options['head'] ?? true);

        if ($this->processedScripts === null) {
            $local  = $this->parseLocalResources($this->resources->get('compiled'));
            $remote = $this->resources->get('scripts.remote');

            $this->processedScripts = [
                'head'  => $this->mergeResourceArrays($local['head'] ?? null, $remote['head'] ?? null),
                'body'  => $this->mergeResourceArrays($local['body'] ?? null, $remote['body'] ?? null),
            ];

            if ($this->processedScripts['head'] !== null) {
                ksort($this->processedScripts['head']);
            }

            if ($this->processedScripts['body'] !== null) {
                ksort($this->processedScripts['body']);
            }
        }

        return $this->processedScripts[$location] !== null ? $this->processedScripts[$location] : null;
    }

    /**
     * Group resources together by type
     *
     * @param array $compiled
     * @return array|null
     */
    protected function parseLocalResources(?array $compiled): ?array
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
                $compiledResource           = sprintf('compiled_%s.js', $this->crc($type . $script));
                $compiledResourceFilePath   = $this->getResourcePath($compiledResource, $theme);

                if (!is_file($compiledResourceFilePath)) {
                    $this->mergeResources(
                        sprintf('scripts.local.%s.%s', $theme->getKey(), $type),
                        $compiledResourceFilePath
                    );
                }

                $location = $this->getLocation(strpos($type, 'head') !== false);

                $scripts[$location][] = $this->createLocalResourceFileSettings($theme, $type, $compiledResource);
            }
        }

        return $scripts;
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
            $str = <<<'SCRIPTSTR'
<script type="application/javascript" src="{{url}}"{{async}}{{defer}}{{integ}}{{cors}}></script>
SCRIPTSTR;

            $output[] = Str::format($str, [
                'url'   => $this->url->create(null, $resource->url, $resource->filename),
                'async' => $resource->async ? ' async' : null,
                'defer' => $resource->defer ? ' defer' : null,
                'integ' => $resource->integrity !== null ? ' integrity="' . $resource->integrity . '"' : null,
                'cors'  => $resource->crossorigin !== null ? ' crossorigin="' . $resource->crossorigin . '"' : null,
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
     * @return \Laranix\Themer\ResourceSettings|\Laranix\Themer\Scripts\Settings
     */
    protected function createLocalResourceFileSettings(Theme $theme, string $type, string $resource): ResourceSettings
    {
        return new Settings([
            'key'       => $this->crc(sprintf('%s%s%s', $theme->getKey(), $type, $resource)),
            'resource'  => $resource,
            'url'       => $this->getBaseUrl($theme),
            'theme'     => $theme,
            'async'     => strpos($type, 'async') !== false,
            'defer'     => strpos($type, 'defer') !== false,
            'order'     => ++$this->order,
        ]);
    }

    /**
     * Set the subdirectory in the theme for the resource type
     *
     * @return string
     */
    protected function getDirectory(): string
    {
        return 'scripts';
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
     * @param \Laranix\Themer\ResourceSettings|Settings $settings
     * @return string
     */
    protected function getAttributes(ResourceSettings $settings) : string
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
