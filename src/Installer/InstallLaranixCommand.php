<?php
namespace Laranix\Installer;

use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Console\Command;
use FilesystemIterator;
use Symfony\Component\Console\Output\OutputInterface;

class InstallLaranixCommand extends Command
{
    use DetectsApplicationNamespace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laranix:install
                            {--O|overwrite : Overwrite files if they exist}
                            {--A|all : Will include all options}
                            {--C|configs : Publish Laranix configs}
                            {--K|packageconf : Publish other configs from packages}
                            {--T|controllers : Install controllers}
                            {--S|services : Install services}
                            {--R|routes : Copy routes}
                            {--H|themes : Copy themes}
                            {--W|views : Copy views}
                            {--G|migrations : Publish migrations}
                            {--D|seeds : Install DB seeds}
                            {--M|mail : Publish mail views (Laravel)}
                            {--P|prompt : Prompt for overwrites}';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Install Laranix';

    /**
     * All options
     *
     * @var array
     */
    protected $allOptions;

    /**
     * If files should be overwritten
     *
     * @var bool
     */
    protected $overwrite = false;

    /**
     * Prompt to overwrite
     *
     * @var bool
     */
    protected $confirmOverwrite = false;

    /**
     * Run the command
     */
    public function fire()
    {
        $this->allOptions = $this->options();
        $this->overwrite = $this->optionSet('overwrite');
        $this->confirmOverwrite = $this->optionSet('prompt');

        $this->installLaranix();
    }

    /**
     * Install Laranix components
     */
    protected function installLaranix()
    {
        $appPath            = app_path();
        $httpPath           = app_path('Http');
        $replaceAppFiles    = ['.stub', '.php'];

        $parts = [
            'Controllers'   => [$httpPath, true, false, $replaceAppFiles],
            'Services'      => [$appPath, true, false, $replaceAppFiles],
            'themes'        => [public_path(), false, true, []],
            'views'         => [resource_path(), false, true, ['.view', '.blade.php']],
            'seeds'         => [database_path(), true, false, $replaceAppFiles],
        ];

        foreach ($parts as $type => $params) {
            $this->installFiles($type, $params[0], $params[1], $params[2], $params[3]);
        }

        $this->copyRoutes();
        $this->publishFiles('Configs');
        $this->publishFiles('Migrations');
        $this->publishOtherPackageConfigs();
        $this->publishMailMarkdown();
    }

    /**
     * Export files
     *
     * @param string $type
     * @param string $dst
     * @param bool   $compile
     * @param bool   $forceLower
     * @param array  $extReplace
     */
    protected function installFiles(string $type, string $dst, bool $compile = true, bool $forceLower = false, array $extReplace = [])
    {
        if (!$this->optionSet(strtolower($type))) {
            $this->info("Skipping {$type}", OutputInterface::VERBOSITY_VERBOSE);
            return;
        }

        $this->info("Installing {$type}...");

        $templates = $this->getFiles($forceLower ? strtolower($type) : $type);

        foreach ($templates as $dir => $files) {
            $this->copyTemplates($dir, $files, $dst, $extReplace, $compile);
        }
    }

    /**
     * Export routes
     *
     * This will overwrite the web.php routes file
     */
    protected function copyRoutes()
    {
        if (!$this->optionSet('routes')) {
            $this->info('Skipping Routes', OutputInterface::VERBOSITY_VERBOSE);
            return;
        }

        if (!$this->overwrite && !$this->confirmOverwrite) {
            $this->info('Overwrite is disabled, skipping Routes', OutputInterface::VERBOSITY_VERBOSE);
            return;
        }

        if (!$this->confirmOverwrite('routes')) {
            return;
        }

        $this->info("Copying Routes...");

        file_put_contents(
            base_path('routes/web.php'),
            file_get_contents(__DIR__ . '/templates/routes/home.stub') . str_replace('<?php', '', file_get_contents(__DIR__ . '/templates/routes/auth.stub'))
        );
    }

    /**
     * Publish Laravel mail markdown templates
     */
    protected function publishMailMarkdown()
    {
        if (!$this->optionSet('mail')) {
            $this->info('Skipping markdown mail (Laravel)', OutputInterface::VERBOSITY_VERBOSE);
            return;
        }

        if (!$this->confirmOverwrite('mail templates')) {
            return;
        }

        $this->info('Publishing markdown mail templates (Laravel)...' . ($this->overwrite || $this->confirmOverwrite ? ' (Using --force flag)' : ''));

        $this->call('vendor:publish', [
            '--tag'     => 'laravel-mail',
            '--force'   => $this->overwrite || $this->confirmOverwrite,
        ]);
    }

    /**
     * Publish other configs
     */
    protected function publishOtherPackageConfigs()
    {
        if (!$this->optionSet('packageconf')) {
            $this->info('Skipping other configs (Laravel)', OutputInterface::VERBOSITY_VERBOSE);
            return;
        }

        if (!$this->confirmOverwrite('other package configs')) {
            return;
        }

        $this->info('Publishing other package configs...' . ($this->overwrite || $this->confirmOverwrite ? ' (Using --force flag)' : ''));

        $this->call('vendor:publish', [
            '--provider'    => 'Indal\Markdown\MarkdownServiceProvider',
            '--force'       => $this->overwrite || $this->confirmOverwrite,
        ]);
    }

    /**
     * Publish files using vendor:publish
     *
     * @param string $name
     */
    protected function publishFiles(string $name)
    {
        $lowerName = strtolower($name);

        if (!$this->optionSet($lowerName)) {
            $this->info("Skipping {$name}", OutputInterface::VERBOSITY_VERBOSE);
            return;
        }

        if (!$this->confirmOverwrite($name)) {
            return;
        }

        $this->warn("Publishing {$name}..." . ($this->overwrite || $this->confirmOverwrite ? ' (Using --force flag)' : ''));

        $this->call('vendor:publish', [
            '--tag'     => "laranix-{$lowerName}",
            '--force'   => $this->overwrite || $this->confirmOverwrite
        ]);
    }

    /**
     * Copy template files and folders
     *
     * @param string $srcDir
     * @param array  $files
     * @param string $dstDir
     * @param array  $extReplace
     * @param bool   $compile
     */
    protected function copyTemplates(string $srcDir, array $files, string $dstDir, array $extReplace = [], bool $compile = false)
    {
        foreach ($files as $file) {
            $fullSrc = $this->getPath("{$srcDir}/{$file}");
            $fullDst = "{$dstDir}/{$srcDir}/" . (!empty($extReplace) ? str_replace($extReplace[0], $extReplace[1], $file) : $file);

            $this->createDirectory("{$dstDir}/{$srcDir}");

            $this->copyFile($fullSrc, $fullDst, $compile);
        }
    }

    /**
     * Copy file to location
     *
     * @param string $src
     * @param string $dst
     * @param bool   $compile
     */
    protected function copyFile(string $src, string $dst, bool $compile)
    {
        if (is_file($dst) && !$this->overwrite && !$this->confirmOverwrite) {
            $this->info("File exists and overwrite disabled, skipping {$dst}", OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        if (!$this->confirmOverwrite($dst)) {
            return;
        }

        $this->info("Copying '{$src}' to '{$dst}'", OutputInterface::VERBOSITY_VERY_VERBOSE);

        file_put_contents($dst, $compile ? $this->compileFile($src) : file_get_contents($src));
    }

    /**
     * Load files in templates directory
     *
     * @param string $relativeDir
     * @param array  $filesystem
     * @return array
     */
    protected function getFiles(string $relativeDir, array &$filesystem = []) : array
    {
        $fullDir = $this->getPath($relativeDir);

        $dirIterator = new FilesystemIterator($fullDir, FilesystemIterator::SKIP_DOTS);

        /** @var \SplFileInfo $info */
        foreach ($dirIterator as $info) {
            if ($info->isDir()) {
                $this->getFiles("{$relativeDir}/{$info->getFilename()}", $filesystem);
            } else {
                if (!isset($filesystem[$relativeDir])) {
                    $filesystem[$relativeDir] = [];
                }

                $filesystem[$relativeDir][] = $info->getFilename();
            }
        }

        return $filesystem;
    }

    /**
     * Get full directory from relative path
     *
     * @param string $relative
     * @return string
     */
    protected function getPath(string $relative) : string
    {
        return realpath(__DIR__.'/templates/'.trim($relative, '/'));
    }

    /**
     * Confirm an overwrite
     *
     * @param string $dest
     * @return bool
     */
    protected function confirmOverwrite(string $dest) : bool
    {
        if (!$this->confirmOverwrite) {
            return true;
        }

        return $this->confirm("Do you wish to overwrite {$dest}?");
    }

    /**
     * Check if option is set
     *
     * @param string $option
     * @return bool
     */
    protected function optionSet(string $option) : bool
    {
        return isset($this->allOptions[$option]) && $this->allOptions[$option] === true
            || (isset($this->allOptions['all']) && $this->allOptions['all'] === true && $option !== 'overwrite' && $option !== 'prompt');
    }

    /**
     * Compile namespace in to file
     *
     * @param string $file
     * @return string
     */
    protected function compileFile(string $file) : string
    {
        return str_replace(
            '__UserAppNamespace__',
            $this->getAppNamespace(),
            file_get_contents($file)
        );
    }

    /**
     * Create directory
     *
     * @param string $path
     * @param int    $permissions
     */
    protected function createDirectory(string $path, int $permissions = 0755)
    {
        if (!is_dir($path)) {
            $this->info("Creating directory '{$path}'", OutputInterface::VERBOSITY_VERBOSE);

            mkdir($path, $permissions, true);
        }
    }
}
