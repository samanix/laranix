<?php
namespace Laranix\Tests\Laranix\Themer\Style;

use Illuminate\Log\Writer;
use Illuminate\View\Factory;
use Laranix\Support\Exception\KeyExistsException;
use Laranix\Themer\Style\Style;
use Laranix\Themer\Style\Settings;
use Laranix\Tests\LaranixTestCase;
use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Themer\Themer;
use Laranix\Themer\ThemeRepository;
use Laranix\Tests\Laranix\Themes\Stubs\Themes;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class StyleTest extends LaranixTestCase
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
     * Test adding a file
     */
    public function testAddLocalFile()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertTrue($style->files->has('_added.foo.foo'));
        $this->assertTrue($style->files->has('_added.bar.baz'));
        $this->assertCount(4, $style->files->get('_added.foo'));
        $this->assertCount(2, $style->files->get('_added.bar'));

        $this->assertCount(2, $style->files->get('style.local.foo.' . $this->crc('print')));
        $this->assertCount(1, $style->files->get('style.local.foo.' . $this->crc('screen and (max-width:1000px)')));
        $this->assertCount(2, $style->files->get('style.local.foo.' . $this->crc('all')));
        $this->assertCount(1, $style->files->get('style.local.bar.' . $this->crc('all')));
    }

    /**
     * Add files with auto ordering
     */
    public function testAddFileWithAutomaticOrdering()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertSame(1, $style->files->get('style.local.foo.' . $this->crc('all') . '.1')->order);
        $this->assertSame(2, $style->files->get('style.local.foo.' . $this->crc('all') . '.2')->order);

        $this->assertSame(4, $style->files->get('style.local.foo.' . $this->crc('print') . '.4')->order);
    }

    /**
     * Add files with auto minification search
     */
    public function testAddFileWithAutoMin()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertSame('style.min.css', $style->files->get('style.local.foo.' . $this->crc('print') . '.3')->file);
        $this->assertSame('foostyle.css', $style->files->get('style.local.foo.' . $this->crc('screen and (max-width:1000px)') . '.10')->file);
    }

    /**
     * Add a file that doesn't exist in given theme, but does in default
     */
    public function testAddFileWithDefaultFallback()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertTrue($this->themer->themeIsDefault($style->files->get('style.local.foo.' . $this->crc('screen and (max-width:1000px)') . '.10')->theme));
    }

    /**
     * Test adding same file twice
     */
    public function testAddKeyWhenKeyExists()
    {
        $this->expectException(KeyExistsException::class);

        $style = $this->createStyle();

        $settings = $this->getSettings(['key' => 'foo', 'file' => 'style.css', 'order' => 1]);
        $settings2 = $this->getSettings(['key' => 'foo', 'file' => 'style2.css', 'order' => 1]);

        $style->add($settings);
        $style->add($settings2);
    }

    /**
     * Test adding a script with no default fallback
     */
    public function testAddNonExistentScriptWithNoDefaultFallback()
    {
        $this->expectException(FileNotFoundException::class);

        $style = $this->createStyle(FileNotFoundException::class);

        $style->add($this->getSettings(['key' => 'foo',   'file' => 'foostyle.css',  'themeName' => 'bar',  'defaultFallback' => false]));
    }

    /**
     * Test add remote scripts
     */
    public function testAddRemoteScripts()
    {
        $style = $this->createStyle();

        $this->loadRemoteStyle($style);

        $this->assertTrue($style->files->has('_added.foo.remote_foo'));
        $this->assertTrue($style->files->has('_added.foo.remote_baz'));
        $this->assertCount(5, $style->files->get('_added.foo'));

        $this->assertCount(6, $style->files->get('style.remote'));
    }

    /**
     * Test adding file without merging
     */
    public function testAddFileWithoutMerging()
    {
        $this->config->set('app.env', 'env1');

        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertNull($style->files->get('style.local'));

        $this->assertCount(6, $style->files->get('style.remote'));

        $this->assertNotNull($style->files->get('style.remote.1')->url);
    }

    /**
     * View should render null with no scripts
     */
    public function testRenderWhenNoScriptsAdded()
    {
        $this->assertNull($this->createStyleWithView(true)->render());
    }

    /**
     * Non existent view should throw exception
     */
    public function testRenderWhenNoViewSet()
    {
        $this->expectException(FileNotFoundException::class);

        $this->createStyleWithView(false)->render([], 'noviewhere');
    }

    /**
     * Test render
     */
    public function testRenderReturnsExpected()
    {
        $style = $this->createStyleWithView(true);

        $this->loadLocalStyle($style);

        $this->assertSame('rendered', $style->render());
    }

    /**
     * Test get file path
     */
    public function testGetFilePath()
    {
        $script = $this->createStyle();

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/foo/style/style.css'),
                          $script->getFilePath('style.css'));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/style/style2.css'),
                          $script->getFilePath('style2.css', $this->themer->getTheme('bar')));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/style/style.min.css'),
                          $script->getFilePath('style.min.css', $this->themer->getTheme('bar')));
    }

    /**
     * Test web path
     */
    public function testGetWebPath()
    {
        $script = $this->createStyle();

        $this->assertSame(config('app.url') . '/themes/foo/style/style.css',
                          $script->getWebUrl('style.css'));

        $this->assertSame('https://www.bar.com/themes/bar/style/style.min.css',
                          $script->getWebUrl('style.min.css', $this->themer->getTheme('bar')));

        $this->assertSame('https://www.baz.com/themes/baz/style/style2.css',
                          $script->getWebUrl('style2.css', $this->themer->getTheme('baz')));
    }

    /**
     * @param string $throw
     * @return \Laranix\Themer\Style\Style
     */
    protected function createStyle($throw = KeyExistsException::class)
    {
        $writer = m::mock(Writer::class);
        $writer->shouldReceive('warning')->andThrow($throw);

        return new Style($this->themer, $this->config, $writer, m::mock(Factory::class));
    }

    /**
     * @param $exists
     * @return \Laranix\Themer\Style\Style
     */
    protected function createStyleWithView(bool $exists = false)
    {
        $writer = m::mock(Writer::class);
        $writer->shouldReceive('warning')->andThrow(KeyExistsException::class);

        $viewfactory = m::mock(Factory::class);

        $viewfactory->shouldReceive('exists')->andReturn($exists);
        $viewfactory->shouldReceive('make')->andReturnSelf();
        $viewfactory->shouldReceive('render')->andReturn('rendered');

        return new Style($this->themer, $this->config, $writer, $viewfactory);
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

        $settings->hasRequired();

        return $settings;
    }

    /**
     * Get local scripts
     *
     * @param \Laranix\Themer\Style\Style $style
     */
    protected function loadLocalStyle(Style $style)
    {
        $local = [
            'foo'       => $this->getSettings(['key' => 'foo',      'file' => 'style.css',  'order' => 1]),
            'bar'       => $this->getSettings(['key' => 'bar',      'file' => 'style2.css',  'order' => 1]),
            'baz'       => $this->getSettings(['key' => 'baz',      'file' => 'style.css', 'order' => 2, 'themeName' => 'bar']),
            'foobar'    => $this->getSettings(['key' => 'foobar',   'file' => 'style.css', 'automin' => true, 'media' => 'print']),
            'barbaz'    => $this->getSettings(['key' => 'barbaz',   'file' => 'style2.css',  'media' => 'print']),
            'foobaz'    => $this->getSettings(['key' => 'foobaz',   'file' => 'foostyle.css',  'themeName' => 'bar',  'order' => 10,  'automin' => true, 'media' => 'screen and (max-width:1000px)']),
        ];

        foreach ($local as $key => $setting) {
            $style->add($setting);
        }
    }

    /**
     * Get remote scripts
     *
     * @param \Laranix\Themer\Style\Style $style
     */
    protected function loadRemoteStyle(Style $style)
    {
        $remote = [
            'remote_foo'       => $this->getSettings(['key' => 'remote_foo',      'file' => 'style.css',    'url' => 'http://foo.com', 'order' => 1]),
            'remote_bar'       => $this->getSettings(['key' => 'remote_bar',      'file' => 'style2.css',   'url' => 'http://foo.com/foo', 'order' => 1]),
            'remote_baz'       => $this->getSettings(['key' => 'remote_baz',      'file' => 'style.css',    'url' => 'http://foo.com/baz/', 'order' => 2]),
            'remote_foobar'    => $this->getSettings(['key' => 'remote_foobar',   'file' => 'style.css',    'url' => 'http://foo.com/style', 'automin' => true, 'media' => 'print']),
            'remote_barbaz'    => $this->getSettings(['key' => 'remote_barbaz',   'file' => 'style2.css',   'url' => '//foo.com', 'media' => 'print']),
            'remote_foobaz'    => $this->getSettings(['key' => 'remote_foobaz',   'file' => 'foostyle.css', 'url' => 'http://foo.com/20', 'themeName' => 'bar',  'order' => 10,  'automin' => true, 'media' => 'screen and (max-width:1024px)']),
        ];

        foreach ($remote as $key => $setting) {
            $style->add($setting);
        }
    }

    /**
     * CRC a string
     *
     * @param string $value
     * @return string
     */
    protected function crc(string $value) : string
    {
        return hash('crc32', $value);
    }
}
