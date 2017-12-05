<?php
namespace Laranix\Themer;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository;
use Laranix\AntiSpam\Recaptcha\Recaptcha;
use Laranix\Themer\Scripts\Scripts;
use Laranix\Themer\Styles\Styles;
use Laranix\Themer\Images\Images;

trait LoadsThemer
{
    /**
     * @var \Laranix\Themer\ThemerResource
     */
    protected $styles;

    /**
     * @var \Laranix\Themer\ThemerResource
     */
    protected $scripts;

    /**
     * Initialise and load themer components
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    protected function loadThemer(Application $app)
    {
        $this->styles     = $app->make(Styles::class);
        $this->scripts    = $app->make(Scripts::class);

        if (method_exists($this, 'share')) {
            $this->share([
                 'styles'  => $this->styles,
                 'scripts' => $this->scripts,
                 'images'  => $app->make(Images::class),
             ]);
        }
    }

    /**
     * Load the default themer files
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function loadThemerDefaultFiles(Repository $config)
    {
        $this->loadStylesheets(...$config->get('themerdefaultfiles.styles.global'));
        $this->loadScripts(...$config->get('themerdefaultfiles.scripts.global'));
    }

    /**
     * Load the default themer files
     *
     * @param \Illuminate\Contracts\Config\Repository       $config
     * @param \Laranix\AntiSpam\Recaptcha\Recaptcha|null    $recaptcha
     * @param array                                         $scripts
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function loadThemerDefaultFormFiles(Repository $config, ?Recaptcha $recaptcha, ...$scripts)
    {
        $this->loadStylesheets(...$config->get('themerdefaultfiles.styles.form'));

        $this->loadScripts(...array_merge($config->get('themerdefaultfiles.scripts.form'), ...$scripts));

        if ($recaptcha !== null && $recaptcha->enabled()) {
            $this->loadScript($config->get('themerdefaultfiles.scripts.recaptcha'));
        }
    }

    /**
     * Add a stylesheet
     *
     * @param \Laranix\Themer\Styles\Settings|array|null $settings
     * @return $this
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function loadStylesheet($settings)
    {
        if ($this->styles !== null) {
            $this->styles->add($settings);
        }

        return $this;
    }

    /**
     * Add multiple stylesheets
     *
     * @param mixed ...$styles
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function loadStylesheets(...$styles)
    {
        $files = $this->getFilePayload($styles ?? null);

        if (empty($files)) {
            return;
        }

        foreach ($files as $style) {
            $this->loadStylesheet($style);
        }
    }

    /**
     * Add a script
     *
     * @param \Laranix\Themer\ResourceSettings|array|null $settings
     * @return $this
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function loadScript($settings)
    {
        if ($this->scripts !== null) {
            $this->scripts->add($settings);
        }

        return $this;
    }

    /**
     * Add multiple scripts
     *
     * @param array $scripts
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function loadScripts(...$scripts)
    {
        $files = $this->getFilePayload($scripts ?? null);

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
