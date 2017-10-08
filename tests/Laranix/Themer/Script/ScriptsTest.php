<?php
namespace Laranix\Tests\Laranix\Themer\Scripts;

use Illuminate\Log\Writer;
use Laranix\Support\Exception\InvalidInstanceException;
use Laranix\Support\Exception\KeyExistsException;
use Laranix\Themer\Scripts\Scripts;
use Laranix\Themer\Scripts\Settings;
use Laranix\Tests\LaranixTestCase;
use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Themer\Themer;
use Laranix\Themer\ThemeRepository;
use Laranix\Tests\Laranix\Themes\Stubs\Themes;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Laranix\Support\IO\Url\Url;

class ScriptsTest extends LaranixTestCase
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
        $script = $this->createScript();

        $script->add([
            'key'       => 'foo',
            'filename'  => 'script.js',
            'order'     => 1
        ]);

        $this->assertTrue($script->resources->has('_added.foo.foo'));
    }

    /**
     * Test adding resource when not an array or settings instance
     */
    public function testAddResourceWithInvalidSettings()
    {
        $this->expectException(InvalidInstanceException::class);

        $script = $this->createScript();

        $script->add('string');
    }

    /**
     * Test adding a resource
     */
    public function testAddLocalResource()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertTrue($script->resources->has('_added.foo.foo'));
        $this->assertTrue($script->resources->has('_added.bar.baz'));
        $this->assertCount(4, $script->resources->get('_added.foo'));
        $this->assertCount(2, $script->resources->get('_added.bar'));


        $this->assertCount(1, $script->resources->get('scripts.local.foo.body_async'));
    }

    /**
     * Add resources with auto ordering
     */
    public function testAddResourceWithAutomaticOrdering()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertSame(2, $script->resources->get('scripts.local.foo.head_defer.2')->order);
        $this->assertSame(3, $script->resources->get('scripts.local.foo.head.3')->order);
    }

    /**
     * Add resources with auto minification search
     */
    public function testAddResourceWithAutoMin()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertSame('script.min.js', $script->resources->get('scripts.local.foo.head.3')->filename);
        $this->assertSame('fooscript.js', $script->resources->get('scripts.local.foo.head_defer.10')->filename);
    }

    /**
     * Add a resource that doesn't exist in given theme, but does in default
     */
    public function testAddResourceWithDefaultFallback()
    {
        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertTrue($this->themer->themeIsDefault($script->resources->get('scripts.local.foo.head_defer.10')->theme));
    }

    /**
     * Test adding same resource twice
     */
    public function testAddKeyWhenKeyExists()
    {
        $this->expectException(KeyExistsException::class);

        $script = $this->createScript();

        $settings = $this->getSettings(['key' => 'foo', 'filename' => 'script.js', 'order' => 1]);
        $settings2 = $this->getSettings(['key' => 'foo', 'filename' => 'script2.js', 'order' => 1]);

        $script->add($settings);
        $script->add($settings2);
    }

    /**
     * Test adding a script with no default fallback
     */
    public function testAddNonExistentResourceWithNoDefaultFallback()
    {
        $this->expectException(FileNotFoundException::class);

        $script = $this->createScript(FileNotFoundException::class);

        $script->add($this->getSettings(['key' => 'foo',   'filename' => 'fooscript.js',  'themeName' => 'bar',  'defaultFallback' => false]));
    }

    /**
     * Test add remote scripts
     */
    public function testAddRemoteResource()
    {
        $script = $this->createScript();

        $this->loadRemoteScripts($script);

        $this->assertTrue($script->resources->has('_added.foo.remote_foo'));
        $this->assertTrue($script->resources->has('_added.foo.remote_baz'));
        $this->assertCount(6, $script->resources->get('_added.foo'));


        $this->assertCount(4, $script->resources->get('scripts.remote.head'));
        $this->assertCount(2, $script->resources->get('scripts.remote.body'));
    }

    /**
     * Test adding resource without merging
     */
    public function testAddResourceWithoutMerging()
    {
        $this->config->set('app.env', 'env1');

        $script = $this->createScript();

        $this->loadLocalScripts($script);

        $this->assertNull($script->resources->get('scripts.local'));

        $this->assertCount(5, $script->resources->get('scripts.remote.head'));
        $this->assertCount(1, $script->resources->get('scripts.remote.body'));

        $this->assertNotNull($script->resources->get('scripts.remote.head.1')->url);
    }

    /**
     * Output null with no scripts
     */
    public function testOutputWhenNoResourcesAdded()
    {
        $script = $this->createScript();

        $this->assertNull($script->output());
    }

    /**
     * Test output
     */
    public function testOutputReturnsExpected()
    {
        $script = $this->createScript();

        $script->add(['key' => 'foo1', 'filename' => 'script1.js', 'url' => 'http://url.com', 'async' => true]);
        $script->add(['key' => 'foo2', 'filename' => 'script2.js', 'url' => 'http://url.com', 'defer' => false]);
        $script->add(['key' => 'foo3', 'filename' => 'script3.js', 'url' => 'http://url.com', 'crossorigin' => 'anonymous', 'integrity' => 'sha1-123']);

        $expect = /** @lang text */
            <<<EXPECTED
<script type="application/javascript" src="http://url.com/script1.js" async defer></script>
<script type="application/javascript" src="http://url.com/script2.js"></script>
<script type="application/javascript" src="http://url.com/script3.js" defer integrity="sha1-123" crossorigin="anonymous"></script>
EXPECTED;

        $this->assertSame($expect, $script->output());
    }

    /**
     * Test get resource path
     */
    public function testGetResourcePath()
    {
        $script = $this->createScript();

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/foo/scripts/script.js'),
                          $script->getResourcePath('script.js'));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/scripts/script2.js'),
                          $script->getResourcePath('script2.js', $this->themer->getTheme('bar')));

        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/scripts/script.min.js'),
                          $script->getResourcePath('script.min.js', $this->themer->getTheme('bar')));
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
     * @return \Laranix\Themer\Scripts\Scripts
     */
    protected function createScript($throw = KeyExistsException::class)
    {
        $writer = m::mock(Writer::class);
        $writer->shouldReceive('warning')->andThrow($throw);

        return new Scripts($this->themer, $this->config, $writer, new Url('http://homestead.app'));
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
     * @param \Laranix\Themer\Scripts\Scripts $script
     */
    protected function loadLocalScripts(Scripts $script)
    {
        $local = [
            'foo'       => $this->getSettings(['key' => 'foo',      'filename' => 'script.js',  'order' => 1]),
            'bar'       => $this->getSettings(['key' => 'bar',      'filename' => 'script.js',  'order' => 1]),
            'baz'       => $this->getSettings(['key' => 'baz',      'filename' => 'script2.js', 'order' => 2, 'themeName' => 'bar']),
            'foobar'    => $this->getSettings(['key' => 'foobar',   'filename' => 'script.js', 'automin' => true, 'defer' => false]),
            'barbaz'    => $this->getSettings(['key' => 'barbaz',   'filename' => 'script.js',  'defer' => false, 'async' => true,            'head' => false]),
            'foobaz'    => $this->getSettings(['key' => 'foobaz',   'filename' => 'fooscript.js',  'themeName' => 'bar',  'order' => 10,  'automin' => true]),
        ];

        foreach ($local as $key => $setting) {
            $script->add($setting);
        }
    }

    /**
     * Get remote scripts
     *
     * @param \Laranix\Themer\Scripts\Scripts $script
     */
    protected function loadRemoteScripts(Scripts $script)
    {
        $remote = [
            'remote_foo'       => $this->getSettings(['key' => 'remote_foo',    'filename' => 'script.js', 'url' => 'http://foo.com', 'order' => 1]),
            'remote_bar'       => $this->getSettings(['key' => 'remote_bar',    'filename' => 'script.js', 'url' => 'http://bar.com/foo', 'order' => 1]),
            'remote_baz'       => $this->getSettings(['key' => 'remote_baz',    'filename' => 'script2.js', 'url' => 'http://foo.com/baz/', 'head' => false]),
            'remote_foobar'    => $this->getSettings(['key' => 'remote_foobar', 'filename' => 'script2.js', 'url' => 'https://foo.com/script', 'defer' => false]),
            'remote_barbaz'    => $this->getSettings(['key' => 'remote_barbaz', 'filename' => 'script.js', 'url' => '//foo.com', 'defer' => false, 'async' => true, 'head' => false]),
            'remote_foobaz'    => $this->getSettings(['key' => 'remote_foobaz', 'filename' => 'script.js', 'url' => 'http://foo.com/20', 'order' => 20]),
        ];

        foreach ($remote as $key => $setting) {
            $script->add($setting);
        }
    }
}
