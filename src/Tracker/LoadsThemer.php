<?php
namespace Laranix\Themer;

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Config\Repository;
use Laranix\Themer\Script\{Script, Settings as ScriptSettings};
use Laranix\Themer\Style\{Style, Settings as StyleSettings};
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
        $this->loadDefaultSheets($config->get('sheets'));
        $this->loadDefaultScripts($config->get('scripts'));
    }

    /**
     * Load default stylesheets
     *
     * @param array|null $files
     */
    protected function loadDefaultSheets(?array $files = [])
    {
        foreach ($files as $file) {
            $this->style->add(new StyleSettings($file));
        }
    }

    /**
     * Load default scripts
     *
     * @param array|null $files
     */
    protected function loadDefaultScripts(?array $files = [])
    {
        foreach ($files as $file) {
            $this->scripts->add(new ScriptSettings($file));
        }
    }

    /**
     * Add a stylesheet
     *
     * @param \Laranix\Themer\Style\Settings|array $settings
     * @return $this
     */
    protected function addStylesheet($settings)
    {
        $this->style->add($settings);

        return $this;
    }

    /**
     * @param array $sheets
     */
    protected function addStylesheets(array $sheets)
    {
        foreach ($sheets as $sheet) {
            $this->addStylesheet($sheet);
        }
    }

    /**
     * Add a script
     *
     * @param \Laranix\Themer\ResourceSettings|array $settings
     * @return $this
     */
    protected function addScript($settings)
    {
        $this->scripts->add($settings);

        return $this;
    }

    /**
     * Add multiple scripts
     *
     * @param array $scripts
     */
    protected function addScripts(array $scripts)
    {
        foreach ($scripts as $script) {
            $this->addScript($script);
        }
    }
}
