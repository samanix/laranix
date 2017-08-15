<?php
namespace Laranix\Tests\Laranix\Themer\Scripts;

use Illuminate\Log\Writer;
use Illuminate\View\Factory;
use Laranix\Support\Exception\InvalidInstanceException;
use Laranix\Support\Exception\KeyExistsException;
use Laranix\Themer\Script\Script;
use Laranix\Themer\Script\Settings;
use Laranix\Tests\LaranixTestCase;
use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Themer\Themer;
use Laranix\Themer\ThemeRepository;
use Laranix\Tests\Laranix\Themes\Stubs\Themes;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ScriptTest extends LaranixTestCase
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\Themer\Themer
     */
    protected $themer;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = new Repository(Themes::$themes);

        $request = m::mock(Request::class);
        $request->shouldReceive('hasCookie')->andReturn(false);

        $this->themer = new Themer($this->config, $request, new ThemeRepository($this->config));
    }

    /**
     * Test adding file with array settings
     */
    public function testAddFileWithArraySettings()
    {
        $script = $this->createScript();

        $script->add([
            'key'   => 'foo',
            'file'  => 'script.js',
            'order' => 1
        ]);

        $this->assertTrue($script->files->has('_added.foo.foo'));
    }

    /**
     * Test adding file when not an array or settings instance
     */
    public function testAddFileWithInvalidSettings()
    {
        $this->expectException(InvalidInstanceException::class);

        $script = $this->createScript();

        $script->add('string');
    }

    /**
     * Test adding a file
     */
    public function testAddLocalFile()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertTrue($script->files->has('_added.foo.foo'));
        $this->assertTrue($script->files->has('_added.bar.baz'));
        $this->assertCount(4, $script->files->get('_added.foo'));
        $this->assertCount(2, $script->files->get('_added.bar'));


        $this->assertCount(1, $script->files->get('scripts.local.foo.body_async'));
    }

    /**
     * Add files with auto ordering
     */
    public function testAddFileWithAutomaticOrdering()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertSame(2, $script->files->get('scripts.local.foo.head_defer.2')->order);
        $this->assertSame(3, $script->files->get('scripts.local.foo.head.3')->order);
    }

    /**
     * Add files with auto minification search
     */
    public function testAddFileWithAutoMin()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertSame('script.min.js', $script->files->get('scripts.local.foo.head.3')->file);
        $this->assertSame('fooscript.js', $script->files->get('scripts.local.foo.head_defer.10')->file);
    }

    /**
     * Add a file that doesn't exist in given theme, but does in default
     */
    public function testAddFileWithDefaultFallback()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertTrue($this->themer->themeIsDefault($script->files->get('scripts.local.foo.head_defer.10')->theme));
    }

    /**
     * Test adding same file twice
     */
    public function testAddKeyWhenKeyExists()
    {
        $this->expectException(KeyExistsException::class);

        $script = $this->createScript();

        $settings = $this->getSettings(['key' => 'foo', 'file' => 'script.js', 'order' => 1]);
        $settings2 = $this->getSettings(['key' => 'foo', 'file' => 'script2.js', 'order' => 1]);

        $script->add($settings);
        $script->add($settings2);
    }

    /**
     * Test adding a script with no default fallback
     */
    public function testAddNonExistentScriptWithNoDefaultFallback()
    {
        $this->expectException(FileNotFoundException::class);

        $script = $this->createScript(FileNotFoundException::class);

        $script->add($this->getSettings(['key' => 'foo',   'file' => 'fooscript.js',  'themeName' => 'bar',  'defaultFallback' => false]));
    }

    /**
     * Test add remote scripts
     */
    public function testAddRemoteScripts()
    {
        $script = $this->createScript();

        $this->loadRemoteScripts($script);

        $this->assertTrue($script->files->has('_added.foo.remote_foo'));
        $this->assertTrue($script->files->has('_added.foo.remote_baz'));
        $this->assertCount(6, $script->files->get('_added.foo'));


        $this->assertCount(4, $script->files->get('scripts.remote.head'));
        $this->assertCount(2, $script->files->get('scripts.remote.body'));
    }

    /**
     * Test adding file without merging
     */
    public function testAddFileWithoutMerging()
    {
        $this->config->set('app.env', 'env1');

        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertNull($script->files->get('scripts.local'));

        $this->assertCount(5, $script->files->get('scripts.remote.head'));
        $this->assertCount(1, $script->files->get('scripts.remote.body'));

        $this->assertNotNull($script->files->get('scripts.remote.head.1')->url);
    }

    /**
     * View should render null with no scripts
     */
    public function testRenderWhenNoScriptsAdded()
    {
        $script = $this->createScriptWithView(true);

        $this->assertNull($script->render());
    }

    /**
     * Non existent view should throw exception
     */
    public function testRenderWhenNoViewSet()
    {
        $this->expectException(FileNotFoundException::class);

        $this->createScriptWithView(false)->render([], 'noviewhere');
    }

    /**
     * Test render
     */
    public function testRenderReturnsExpected()
    {
        $script = $this->createScriptWithView(true);

        $this->loadLocalScripts($script);

        $this->assertSame('rendered', $script->render());
    }

    /**
     * Test get file path
     */
    public function testGetFilePath()
    {
        $script = $this->createScript();

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/foo/scripts/script.js'),
                          $script->getFilePath('script.js'));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/scripts/script2.js'),
                          $script->getFilePath('script2.js', $this->themer->getTheme('bar')));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/scripts/script.min.js'),
                          $script->getFilePath('script.min.js', $this->themer->getTheme('bar')));
    }

    /**
     * Test web path
     */
    public function testGetWebPath()
    {
        $script = $this->createScript();

        $this->assertSame(config('app.url') . '/themes/foo/scripts/script.js',
                          $script->getWebUrl('script.js'));

        $this->assertSame('https://www.bar.com/themes/bar/scripts/script.min.js',
                          $script->getWebUrl('script.min.js', $this->themer->getTheme('bar')));

        $this->assertSame('https://www.baz.com/themes/baz/scripts/script2.js',
                          $script->getWebUrl('script2.js', $this->themer->getTheme('baz')));
    }

    /**
     * @param string $throw
     * @return \Laranix\Themer\Script\Script
     */
    protected function createScript($throw = KeyExistsException::class)
    {
        $writer = m::mock(Writer::class);
        $writer->shouldReceive('warning')->andThrow($throw);

        return new Script($this->themer, $this->config, $writer, m::mock(Factory::class));
    }

    /**
     * @param $exists
     * @return \Laranix\Themer\Script\Script
     */
    protected function createScriptWithView(bool $exists = false)
    {
        $writer = m::mock(Writer::class);
        $writer->shouldReceive('warning')->andThrow(KeyExistsException::class);

        $viewfactory = m::mock(Factory::class);

        $viewfactory->shouldReceive('exists')->andReturn($exists);
        $viewfactory->shouldReceive('make')->andReturnSelf();
        $viewfactory->shouldReceive('render')->andReturn('rendered');

        return new Script($this->themer, $this->config, $writer, $viewfactory);
    }

    /**
     * Get settings
     *
     * @param array|null $options
     * @return Settings
     */
    protected function getSettings(?array $options = [])
    {
        $settings = new Settings($options);

        $settings->hasRequiredSettings();

        return $settings;
    }

    /**
     * Get local scripts
     *
     * @param \Laranix\Themer\Script\Script $script
     */
    protected function loadLocalScripts(Script $script)
    {
        $local = [
            'foo'       => $this->getSettings(['key' => 'foo',      'file' => 'script.js',  'order' => 1]),
            'bar'       => $this->getSettings(['key' => 'bar',      'file' => 'script.js',  'order' => 1]),
            'baz'       => $this->getSettings(['key' => 'baz',      'file' => 'script2.js', 'order' => 2, 'themeName' => 'bar']),
            'foobar'    => $this->getSettings(['key' => 'foobar',   'file' => 'script.js', 'automin' => true, 'defer' => false]),
            'barbaz'    => $this->getSettings(['key' => 'barbaz',   'file' => 'script.js',  'defer' => false, 'async' => true,            'head' => false]),
            'foobaz'    => $this->getSettings(['key' => 'foobaz',   'file' => 'fooscript.js',  'themeName' => 'bar',  'order' => 10,  'automin' => true]),
        ];

        foreach ($local as $key => $setting) {
            $script->add($setting);
        }
    }

    /**
     * Get remote scripts
     *
     * @param \Laranix\Themer\Script\Script $script
     */
    protected function loadRemoteScripts(Script $script)
    {
        $remote = [
            'remote_foo'       => $this->getSettings(['key' => 'remote_foo',    'file' => 'script.js', 'url' => 'http://foo.com', 'order' => 1]),
            'remote_bar'       => $this->getSettings(['key' => 'remote_bar',    'file' => 'script.js', 'url' => 'http://bar.com/foo', 'order' => 1]),
            'remote_baz'       => $this->getSettings(['key' => 'remote_baz',    'file' => 'script2.js', 'url' => 'http://foo.com/baz/', 'head' => false]),
            'remote_foobar'    => $this->getSettings(['key' => 'remote_foobar', 'file' => 'script2.js', 'url' => 'https://foo.com/script', 'defer' => false]),
            'remote_barbaz'    => $this->getSettings(['key' => 'remote_barbaz', 'file' => 'script.js', 'url' => '//foo.com', 'defer' => false, 'async' => true, 'head' => false]),
            'remote_foobaz'    => $this->getSettings(['key' => 'remote_foobaz', 'file' => 'script.js', 'url' => 'http://foo.com/20', 'order' => 20]),
        ];

        foreach ($remote as $key => $setting) {
            $script->add($setting);
        }
    }
}
