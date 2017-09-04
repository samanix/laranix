<?php
namespace Laranix\Themer\Commands;

use Illuminate\Console\Command;
use Laranix\Themer\Scripts\Scripts;
use Laranix\Themer\Styles\Styles;
use Laranix\Themer\ThemeRepository;
use Illuminate\Filesystem\Filesystem;

class ClearCompiled extends Command
{
    /**
     * @var \Laranix\Themer\ThemeRepository
     */
    protected $themes;

    /**
     * @var \Laranix\Themer\Styles\Styles
     */
    protected $styles;

    /**
     * @var \Laranix\Themer\Scripts\Scripts
     */
    protected $scripts;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laranix:themer:clear {--T|theme=all  : Theme to clear compiled files for}';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Clears compiled themer files for enabled themes';

    /**
     * ClearCompiled constructor.
     *
     * @param \Laranix\Themer\ThemeRepository   $themes
     * @param \Laranix\Themer\Styles\Styles     $styles
     * @param \Laranix\Themer\Scripts\Scripts   $scripts
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     */
    public function __construct(ThemeRepository $themes,
                                Styles $styles,
                                Scripts $scripts,
                                Filesystem $filesystem)
    {
        parent::__construct();

        $this->themes       = $themes;
        $this->styles       = $styles;
        $this->scripts      = $scripts;
        $this->filesystem   = $filesystem;
    }

    /**
     * Run the command
     */
    public function handle()
    {
        $themes = $this->getThemes($this->option('theme'));

        if ($themes === null) {
            $this->error('Error: ' . $this->option('theme') . ' not found');
            return;
        }

        if (!is_array($themes)) {
            $themes = [$themes];
        }

        $this->clearCompiled($themes);
    }

    /**
     * Get theme(s)
     *
     * @return array|\Laranix\Themer\Theme|null
     */
    protected function getThemes(?string $theme)
    {
        if ($theme === null || $theme === 'all') {
            return $this->themes->all();
        }

        try {
            return $this->themes->get($theme, false);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Theme ' . $theme . ' not found');
        }
    }

    /**
     * Clear compiled files
     *
     * @param array $themes
     */
    protected function clearCompiled(array $themes)
    {
        foreach ($themes as $theme) {
            $this->warn('Clearing files for `' . $theme->getKey() . '`');
            $this->info('Clearing styles...');
            $this->clearCompiledFiles($this->styles->getResourcePath('compiled_*.css', $theme));

            $this->info('Clearing scripts...');
            $this->clearCompiledFiles($this->scripts->getResourcePath('compiled_*.js', $theme));

            $this->warn('Done!');
            $this->line('');
        }
    }

    /**
     * Clear compiled files
     *
     * @param string $pattern
     */
    protected function clearCompiledFiles(string $pattern)
    {
        $this->filesystem->delete($this->filesystem->glob($pattern));
    }
}
