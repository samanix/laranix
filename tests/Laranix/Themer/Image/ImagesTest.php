<?php
namespace Laranix\Tests\Laranix\Themer\Image;

use Illuminate\Log\Writer;
use Laranix\Support\Exception\NotImplementedException;
use Laranix\Support\IO\Url\Url;
use Laranix\Themer\Image\Images;
use Laranix\Tests\LaranixTestCase;
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
        $this->assertSame(config('app.url') . '/themes/foo/images/laranix.png', $this->image->getWebUrl('laranix.png'));
        $this->assertSame('https://www.bar.com/themes/bar/images/samanix.png', $this->image->getWebUrl('samanix.png', $this->themer->getTheme('bar')));
        $this->assertSame('https://www.baz.com/themes/baz/images/samanix.png', $this->image->getWebUrl('samanix.png', $this->themer->getTheme('baz')));
    }

    /**
     * Test image display
     */
    public function testDisplayImage()
    {
        $imgurl = $this->image->getWebUrl('laranix.png');
        $imgurl2 = $this->image->getWebUrl('samanix.png');

        $this->assertSame('<img src="' . $imgurl . '" alt="img" title="title" />',
                          $this->image->display('laranix.png', 'img', ['title' => 'title']));

        $this->assertSame('<img src="' . $imgurl . '" alt="laranix.png" />',
                          $this->image->show('laranix.png'));

        $this->assertSame('<img src="' . $imgurl2 . '" alt="samanix.png" />',
                          $this->image->show('samanix.png'));

        $this->assertSame('<img src="' . $imgurl . '" alt="foo" id="fooimage" title="bar" />',
                          $this->image->display(['image' => 'laranix.png', 'alt' => 'foo', 'id' => 'fooimage', 'extra' => ['title' => 'bar']]));

        $this->assertSame('<img src="' . $imgurl . '" alt="foo" id="fooimage" title="baz" />',
                          $this->image->display(['image' => 'laranix.png', 'alt' => 'foo', 'id' => 'fooimage', 'extra' => ['title' => 'bar']], 'bar', ['title' => 'baz']));

        $this->assertSame('<img src="' . $imgurl . '" alt="foo" id="fooimage" title="baz" data-foo="bar" />',
                          $this->image->display(['image' => 'laranix.png', 'alt' => 'foo', 'id' => 'fooimage'], 'bar', ['title' => 'baz', 'data-foo' => 'bar']));
    }
}
