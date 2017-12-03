<?php
namespace Laranix\Tests\Laranix\Themer\Image;

use Illuminate\Log\Writer;
use Laranix\Support\Exception\NotImplementedException;
use Laranix\Support\IO\Url\Url;
use Laranix\Support\IO\Url\UrlSettings;
use Laranix\Themer\Images\Images;
use Laranix\Tests\LaranixTestCase;
use Laranix\Themer\Images\LocalSettings;
use Laranix\Themer\Images\RemoteSettings;
use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Themer\Themer;
use Laranix\Themer\ThemeRepository;
use Laranix\Tests\Laranix\Themes\Stubs\Themes;

class ImageTest extends LaranixTestCase
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\Themer\ThemerResource|Images
     */
    protected $image;

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

        $this->image = new Images($this->themer, $this->config, m::mock(Writer::class), new Url('http://homestead.app'));
    }

    /**
     * Test adding a resource
     */
    public function testAddResource()
    {
        $this->expectException(NotImplementedException::class);

        $this->image->add([]);
    }

    /**
     * Test output resource
     */
    public function testOutputResource()
    {
        $this->expectException(NotImplementedException::class);

        $this->image->output();
    }

    /**
     * Test resource list
     */
    public function testResourceList()
    {
        $this->assertInstanceOf(\Laranix\Support\IO\Repository::class, $this->image->resources);
    }

    /**
     * Test get resource path
     */
    public function testGetResourcePath()
    {
        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/foo/images/laranix.png'), $this->image->getResourcePath('laranix.png'));
        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/images/samanix.png'), $this->image->getResourcePath('samanix.png', $this->themer->getTheme('bar')));
    }

    /**
     * Test web path
     */
    public function testGetWebPath()
    {
        $this->assertSame(config('app.url') . '/themes/foo/images/laranix.png', $this->image->getThemeResourceUrl('laranix.png'));
        $this->assertSame('https://www.bar.com/themes/bar/images/samanix.png', $this->image->getThemeResourceUrl('samanix.png', $this->themer->getTheme('bar')));
        $this->assertSame('https://www.baz.com/themes/baz/images/samanix.png', $this->image->getThemeResourceUrl('samanix.png', $this->themer->getTheme('baz')));
    }

    /**
     * Test image display
     *
     * @dataProvider imageDisplayProvider
     * @param array $args
     */
    public function testDisplayImage(...$args)
    {
        $expected = array_pop($args);

        $this->assertSame($expected, $this->image->display(...$args));
    }

    /**
     * Get image url
     *
     * @dataProvider imageUrlProvider
     * @param array $args
     */
    public function testGetImageUrl(...$args)
    {
        $expected = array_pop($args);

        $this->assertSame($expected, $this->image->url(...$args));
    }

    /**
     * Display provider
     *
     * @return array
     */
    public function imageDisplayProvider()
    {
        return [
            [
                'laranix.png', 'foo', ['id' => 'fooimage', 'title' => 'bar'],
                '<img src="http://homestead.app/themes/foo/images/laranix.png" alt="foo" id="fooimage" title="bar" />'
            ],
            [
                'https://foo.com/bar.jpg',
                '<img src="https://foo.com/bar.jpg" alt="" />'
            ],
            [
                new UrlSettings(['domain' => 'foo.com', 'path' => 'bar.png']),
                '<img src="http://foo.com/bar.png" alt="" />'
            ],
            [
                ['image' => 'laranix.png', 'alt' => 'foo"', 'id' => 'fooimage', 'extra' => ['title' => 'bar']],
                '<img src="http://homestead.app/themes/foo/images/laranix.png" alt="foo&quot;" id="fooimage" title="bar" />'
            ],
            [
                new LocalSettings(['image' => 'laranix.png', 'alt' => 'hello']),
                '<img src="http://homestead.app/themes/foo/images/laranix.png" alt="hello" />'
            ],
            [
                new RemoteSettings(['url' => 'https://foo.com/foo.png', 'alt' => 'hello']),
                '<img src="https://foo.com/foo.png" alt="hello" />'
            ]
        ];
    }

    /**
     * Url provider
     *
     * @return array
     */
    public function imageUrlProvider()
    {
        return [
            [
                'laranix.png', 'foo', ['id' => 'fooimage', 'title' => 'bar'],
                'http://homestead.app/themes/foo/images/laranix.png'
            ],
            [
                'https://foo.com/bar.jpg',
                'https://foo.com/bar.jpg'
            ],
            [
                new UrlSettings(['domain' => 'foo.com', 'path' => 'bar.png']),
                'http://foo.com/bar.png'
            ],
            [
                ['image' => 'laranix.png', 'alt' => 'foo', 'id' => 'fooimage', 'extra' => ['title' => 'bar']],
                'http://homestead.app/themes/foo/images/laranix.png'
            ],
            [
                new LocalSettings(['image' => 'laranix.png', 'alt' => 'hello']),
                'http://homestead.app/themes/foo/images/laranix.png'
            ],
            [
                new RemoteSettings(['url' => 'https://foo.com/foo.png', 'alt' => 'hello']),
                'https://foo.com/foo.png'
            ]
        ];
    }
}
