<?php
namespace Laranix\Themer;

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Config\Repository;
use Laranix\AntiSpam\Recaptcha\Recaptcha;
use Laranix\Themer\Script\Script;
use Laranix\Themer\Style\Style;
use Laranix\Themer\Image\Image;

trait LoadsThemer
{
    /**
     * @var \Laranix\Themer\ThemerResource
     */
    protected $style;

    /**
     * @var \Laranix\Themer\ThemerResource
     */
    protected $scripts;

    /**
     * Initialise and load themer components
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function loadThemer(Application $app)
    {
        $this->style      = $app->make(Style::class);
        $this->scripts    = $app->make(Script::class);

        if (method_exists($this, 'share')) {
            $this->share([
                 'style'   => $this->style,
                 'scripts' => $this->scripts,
                 'image'   => $app->make(Image::class),
             ]);
        }
    }

    /**
     * Load the default themer files
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    protected function loadThemerDefaultFiles(Repository $config)
    {
        $this->loadStylesheets($config->get('sheets.global'));
        $this->loadScripts($config->get('scripts.global'));
    }

    /**
     * Load the default themer files
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Laranix\AntiSpam\Recaptcha\Recaptcha   $recaptcha
     */
    protected function loadThemerDefaultFormFiles(Repository $config, Recaptcha $recaptcha)
    {
        $this->loadStylesheets($config->get('sheets.form'));
        $this->loadScripts($config->get('scripts.form'));

        if ($recaptcha->enabled()) {
            $this->loadScript([
                'key'       => 'recaptcha',
                'filename'  => 'api.js',
                'url'       => 'https://www.google.com/recaptcha',
                'order'     => 10,
                'async'     => true,
            ]);
        }
    }

    /**
     * Add a stylesheet
     *
     * @param \Laranix\Themer\Style\Settings|array|null $settings
     * @return $this
     */
    protected function loadStylesheet($settings)
    {
        if ($settings === null) {
            return $this;
        }

        $this->style->add($settings);

        return $this;
    }

    /**
     * Add multiple stylesheets
     *
     * @param mixed ...$sheets
     */
    protected function loadStylesheets(...$sheets)
    {
        $files = $this->getFilePayload($sheets[0] ?? null);

        if (empty($files)) {
            return;
        }

        foreach ($files as $sheet) {
            $this->loadStylesheet($sheet);
        }
    }

    /**
     * Add a script
     *
     * @param \Laranix\Themer\ResourceSettings|array|null $settings
     * @return $this
     */
    protected function loadScript($settings)
    {
        if ($settings === null || !is_array($settings)) {
            return $this;
        }

        $this->scripts->add($settings);

        return $this;
    }

    /**
     * Add multiple scripts
     *
     * @param array $scripts
     */
    protected function loadScripts(...$scripts)
    {
        $files = $this->getFilePayload($scripts[0] ?? null);

        if (empty($files)) {
            return;
        }

        foreach ($files as $script) {
            $this->loadScript($script);
        }
    }

    /**
     * Get the file payload
     *
     * @param array|null $files
     * @return array|null
     */
    protected function getFilePayload(?array $files) : ?array
    {
        if (empty($files)) {
            return null;
        }

        $files = array_filter($files, function($file) {
            return $file instanceof ResourceSettings || (isset($file['key']) && isset($file['filename']));
        });

        return $files;
    }
}
