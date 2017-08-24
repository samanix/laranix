<?php
namespace Laranix\Tests\Laranix\Support\IO;

use Laranix\Support\IO\Url\Href;
use Laranix\Tests\LaranixTestCase;

class HrefTest extends LaranixTestCase
{
    /**
     * @var \Laranix\Support\IO\Url\Href
     */
    protected $href;

    /**
     * @var string
     */
    protected $baseurl = 'http://homestead.app';

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->href = new Href($this->baseurl);

        $_SERVER['HTTP_HOST']   = str_replace(['http://', 'https://'], '', $this->baseurl);
        $_SERVER['REQUEST_URI'] = '';

        config()->set('app.url', $this->baseurl);
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
    }

    /**
     * Test making an href
     */
    public function testMakeHref()
    {
        $this->assertSame('<a href="#bar">foo</a>', $this->href->create('foo', '#bar'));
        $this->assertSame('<a href="http://foo.com" title="bar">foo</a>', $this->href->create('foo', 'http://foo.com', [ 'title' => 'bar' ]));
    }
}
