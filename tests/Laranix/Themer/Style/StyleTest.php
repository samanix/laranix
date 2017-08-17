<?php
namespace Laranix\Tests\Laranix\Themer\Style;

use Illuminate\Log\Writer;
use Laranix\Support\Exception\InvalidInstanceException;
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
     * Test adding resource with array settings
     */
    public function testAddResourceWithArraySettings()
    {
        $style = $this->createStyle();

        $style->add([
            'key'   => 'foo',
            'filename'  => 'style.css',
            'order' => 1
        ]);

        $this->assertTrue($style->resources->has('_added.foo.foo'));
    }

    /**
     * Test adding resource when not an array or settings instance
     */
    public function testAddResourceWithInvalidSettings()
    {
        $this->expectException(InvalidInstanceException::class);

        $style = $this->createStyle();

        $style->add('string');
    }

    /**
     * Test adding a resource
     */
    public function testAddLocalResource()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertTrue($style->resources->has('_added.foo.foo'));
        $this->assertTrue($style->resources->has('_added.bar.baz'));
        $this->assertCount(4, $style->resources->get('_added.foo'));
        $this->assertCount(2, $style->resources->get('_added.bar'));

        $this->assertCount(2, $style->resources->get('style.local.foo.' . $this->crc('print')));
        $this->assertCount(1, $style->resources->get('style.local.foo.' . $this->crc('screen and (max-width:1000px)')));
        $this->assertCount(2, $style->resources->get('style.local.foo.' . $this->crc('all')));
        $this->assertCount(1, $style->resources->get('style.local.bar.' . $this->crc('all')));
    }

    /**
     * Add resources with auto ordering
     */
    public function testAddResourceWithAutomaticOrdering()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertSame(1, $style->resources->get('style.local.foo.' . $this->crc('all') . '.1')->order);
        $this->assertSame(2, $style->resources->get('style.local.foo.' . $this->crc('all') . '.2')->order);

        $this->assertSame(4, $style->resources->get('style.local.foo.' . $this->crc('print') . '.4')->order);
    }

    /**
     * Add resources with auto minification search
     */
    public function testAddResourceWithAutoMin()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertSame('style.min.css', $style->resources->get('style.local.foo.' . $this->crc('print') . '.3')->filename);
        $this->assertSame('foostyle.css', $style->resources->get('style.local.foo.' . $this->crc('screen and (max-width:1000px)') . '.10')->filename);
    }

    /**
     * Add a resource that doesn't exist in given theme, but does in default
     */
    public function testAddResourceWithDefaultFallback()
    {
        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertTrue($this->themer->themeIsDefault($style->resources->get('style.local.foo.' . $this->crc('screen and (max-width:1000px)') . '.10')->theme));
    }

    /**
     * Test adding same resource twice
     */
    public function testAddKeyWhenKeyExists()
    {
        $this->expectException(KeyExistsException::class);

        $style = $this->createStyle();

        $settings = $this->getSettings(['key' => 'foo', 'filename' => 'style.css', 'order' => 1]);
        $settings2 = $this->getSettings(['key' => 'foo', 'filename' => 'style2.css', 'order' => 1]);

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

        $style->add($this->getSettings(['key' => 'foo',   'filename' => 'foostyle.css',  'themeName' => 'bar',  'defaultFallback' => false]));
    }

    /**
     * Test add remote scripts
     */
    public function testAddRemoteScripts()
    {
        $style = $this->createStyle();

        $this->loadRemoteStyle($style);

        $this->assertTrue($style->resources->has('_added.foo.remote_foo'));
        $this->assertTrue($style->resources->has('_added.foo.remote_baz'));
        $this->assertCount(5, $style->resources->get('_added.foo'));

        $this->assertCount(6, $style->resources->get('style.remote'));
    }

    /**
     * Test adding resources without merging
     */
    public function testAddResourceWithoutMerging()
    {
        $this->config->set('app.env', 'env1');

        $style = $this->createStyle();

        $this->loadLocalStyle($style);

        $this->assertNull($style->resources->get('style.local'));

        $this->assertCount(6, $style->resources->get('style.remote'));

        $this->assertNotNull($style->resources->get('style.remote.1')->url);
    }

    /**
     * Output should be null with no scripts
     */
    public function testOutputWhenNoScriptsAdded()
    {
        $this->assertNull($this->createStyle()->output());
    }

    /**
     * Test output
     */
    public function testOutputReturnsExpected()
    {
        $style = $this->createStyle();

        $style->add(['key' => 'foo1', 'filename' => 'style1.css', 'url' => 'http://url.com', ]);
        $style->add(['key' => 'foo2', 'filename' => 'style2.css', 'url' => 'http://url.com', 'media' => 'screen and (min-width: 768px)']);
        $style->add(['key' => 'foo3', 'filename' => 'style3.css', 'url' => 'http://url.com', 'integrity' => 'sha1-123']);

        $expect = /** @lang text */
            <<<EXPECTED
<link rel="stylesheet" type="text/css" href="http://url.com/style1.css" media="all" />
<link rel="stylesheet" type="text/css" href="http://url.com/style2.css" media="screen and (min-width: 768px)" />
<link rel="stylesheet" type="text/css" href="http://url.com/style3.css" media="all" integrity="sha1-123" />
EXPECTED;

        $this->assertSame($expect, $style->output());
    }

    /**
     * Test get resource path
     */
    public function testGetResourcePath()
    {
        $script = $this->createStyle();

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/foo/style/style.css'),
                          $script->getResourcePath('style.css'));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/style/style2.css'),
                          $script->getResourcePath('style2.css', $this->themer->getTheme('bar')));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/style/style.min.css'),
                          $script->getResourcePath('style.min.css', $this->themer->getTheme('bar')));
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

        return new Style($this->themer, $this->config, $writer);
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
     * @param \Laranix\Themer\Style\Style $style
     */
    protected function loadLocalStyle(Style $style)
    {
        $local = [
            'foo'       => $this->getSettings(['key' => 'foo',      'filename' => 'style.css',  'order' => 1]),
            'bar'       => $this->getSettings(['key' => 'bar',      'filename' => 'style2.css',  'order' => 1]),
            'baz'       => $this->getSettings(['key' => 'baz',      'filename' => 'style.css', 'order' => 2, 'themeName' => 'bar']),
            'foobar'    => $this->getSettings(['key' => 'foobar',   'filename' => 'style.css', 'automin' => true, 'media' => 'print']),
            'barbaz'    => $this->getSettings(['key' => 'barbaz',   'filename' => 'style2.css',  'media' => 'print']),
            'foobaz'    => $this->getSettings(['key' => 'foobaz',   'filename' => 'foostyle.css',  'themeName' => 'bar',  'order' => 10,  'automin' => true, 'media' => 'screen and (max-width:1000px)']),
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
            'remote_foo'       => $this->getSettings(['key' => 'remote_foo',      'filename' => 'style.css',    'url' => 'http://foo.com', 'order' => 1]),
            'remote_bar'       => $this->getSettings(['key' => 'remote_bar',      'filename' => 'style2.css',   'url' => 'http://foo.com/foo', 'order' => 1]),
            'remote_baz'       => $this->getSettings(['key' => 'remote_baz',      'filename' => 'style.css',    'url' => 'http://foo.com/baz/', 'order' => 2]),
            'remote_foobar'    => $this->getSettings(['key' => 'remote_foobar',   'filename' => 'style.css',    'url' => 'http://foo.com/style', 'automin' => true, 'media' => 'print']),
            'remote_barbaz'    => $this->getSettings(['key' => 'remote_barbaz',   'filename' => 'style2.css',   'url' => '//foo.com', 'media' => 'print']),
            'remote_foobaz'    => $this->getSettings(['key' => 'remote_foobaz',   'filename' => 'foostyle.css', 'url' => 'http://foo.com/20', 'themeName' => 'bar',  'order' => 10,  'automin' => true, 'media' => 'screen and (max-width:1024px)']),
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
