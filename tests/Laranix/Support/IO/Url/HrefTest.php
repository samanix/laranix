<?php
namespace Laranix\Tests\Laranix\Support\IO;

use Laranix\Support\IO\Url\Href;
use Laranix\Support\IO\Url\Url;
use Laranix\Support\IO\Url\UrlSettings;
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
    protected $baseurl = 'http://homestead.test';

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->href = new Href($this->baseurl, new Url($this->baseurl));

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
        $this->assertSame('<a href="' . $this->baseurl . '/foo">bar</a>', $this->href->create('bar', 'foo'));
        $this->assertSame('<a href="https://foo.com/bar">bar</a>', $this->href->create('bar', ['scheme' => 'https', 'domain' => 'foo.com', 'path' => 'bar']));
    }

    /**
     * Test making href with _blank target
     */
    public function testMakeHrefWithRel()
    {
        $this->assertSame('<a href="' . $this->baseurl . '/foo" target="_blank" rel="noreferrer noopener">bar</a>',
                          $this->href->create('bar', 'foo', ['target' => '_blank']));

        $this->assertSame('<a href="' . $this->baseurl . '" target="_blank" rel="nofollow">bar</a>',
                          $this->href->create('bar', $this->baseurl, ['target' => '_blank', 'rel' => 'nofollow']));
    }
}
