<?php
namespace Laranix\Themer\Commands;

use Illuminate\Console\Command;
use Laranix\Support\IO\Path;
use Laranix\Themer\ThemeRepository;
use Illuminate\Filesystem\Filesystem;

class LinkDirectory extends Command
{
    /**
     * @var \Laranix\Themer\ThemeRepository
     */
    protected $themes;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laranix:themer:link {theme} {type=images}';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Creates a symbolic link for the themes resource directory (if used) in the public theme folder';

    /**
     * ClearCompiled constructor.
     *
     * @param \Laranix\Themer\ThemeRepository   $themes
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     */
    public function __construct(ThemeRepository $themes, Filesystem $filesystem)
    {
        parent::__construct();

        $this->themes       = $themes;
        $this->filesystem   = $filesystem;
    }

    /**
     * Run the command
     */
    public function handle()
    {
        $theme = $this->getThemes($this->argument('theme'));
        $type = $this->argument('type');

        $path = Path::combine($theme->getPath(), $type);

        if (is_dir($path)) {
            $this->error('The path "' . $path . '" already exists');
            return;
        }

        $this->filesystem->link(
            Path::combine(resource_path(), 'themes', $theme->getKey(), $type),
            $path
        );
    }

    /**
     * Get theme
     *
     * @param string $theme
     * @return \Laranix\Themer\Theme
     */
    protected function getThemes(string $theme)
    {
        try {
            return $this->themes->get($theme, false);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Theme \'' . $theme . '\' not found');
        }
    }
}
