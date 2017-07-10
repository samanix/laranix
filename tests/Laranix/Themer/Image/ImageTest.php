<?php
namespace Tests\Laranix\Themer\Image;

use Illuminate\Log\Writer;
use Illuminate\View\Factory;
use Laranix\Support\Exception\NotImplementedException;
use Laranix\Themer\Image\Image;
use Tests\LaranixTestCase;
use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Themer\Themer;
use Laranix\Themer\ThemeRepository;
use Tests\Laranix\Themes\Stubs\Themes;

class ImageTest extends LaranixTestCase
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\Themer\ThemerFile|Image
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

        $this->image = new Image($this->themer, $this->config, m::mock(Writer::class), m::mock(Factory::class));
    }

    /**
     * Test adding a file
     */
    public function testAddFile()
    {
        $this->expectException(NotImplementedException::class);

        $this->image->add();
    }

    /**
     * Test render file
     */
    public function testRenderFile()
    {
        $this->expectException(NotImplementedException::class);

        $this->image->render();
    }

    /**
     * Test file list
     */
    public function testFileList()
    {
        $this->assertInstanceOf(\Laranix\Support\IO\Repository::class, $this->image->files);
    }

    /**
     * Test get file path
     */
    public function testGetFilePath()
    {
        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/foo/images/laranix.png'), $this->image->getFilePath('laranix.png'));
        $this->assertSame(realpath(__DIR__ . '/../Stubs/themes/bar/images/samanix.png'), $this->image->getFilePath('samanix.png', $this->themer->getTheme('bar')));
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

        $this->assertSame('<img src="' . $imgurl . '" alt="img" title="title" />', $this->image->display('laranix.png', ['alt' => 'img', 'title' => 'title']));
        $this->assertSame('<img src="' . $imgurl . '" alt="img" title="title" />', $this->image->show('laranix.png'));

        $this->assertSame('<img src="' . $imgurl2 . '" alt="samanix.png" />', $this->image->show('samanix.png'));
    }
}
