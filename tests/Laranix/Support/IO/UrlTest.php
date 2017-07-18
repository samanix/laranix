<?php
namespace Laranix\Tests\Laranix\Support\IO;

use Laranix\Support\IO\Url\Url;
use Laranix\Tests\LaranixTestCase;

class UrlTest extends LaranixTestCase
{
    /**
     * @var string
     */
    protected $url = 'http://bar.com';

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $_SERVER['HTTP_HOST']   = 'bar.com';
        $_SERVER['REQUEST_URI'] = '';

        config()->set('app.url', 'http://bar.com');
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
     * Test url creation
     *
     * @dataProvider getUrlComponents
     */
    public function testCreateUrl($a, $b, $c, $d, $e, $f, $g)
    {
        $this->assertSame($g, Url::create($a, $b, $c, $d, $e, $f));
    }

    /**
     * Test simple creation
     */
    public function testCreateToUrl()
    {
        $this->assertSame($this->url . '/bar', Url::to('bar'));
        $this->assertSame($this->url . '/bar/baz', Url::to(['bar', 'baz']));
        $this->assertSame($this->url, Url::to(null));
        $this->assertSame($this->url . '/bar/', Url::to('bar', null, null, true));
        $this->assertSame($this->url . '/', Url::to(null, null, null, true));
        $this->assertSame($this->url . '#bar', Url::to(null, null, 'bar'));
        $this->assertSame($this->url . '/bar/?hello=world', Url::to('bar', [ 'hello' => 'world'], null, true));
        $this->assertSame($this->url . '/bar?hello=world#foo', Url::to('bar', ['hello' => 'world'], 'foo', false));

        $this->assertSame($this->url, Url::self());
    }

    /**
     * Test making url
     */
    public function testMakeUrl()
    {
        $this->assertSame($this->url . '/foo', Url::url('/foo'));
        $this->assertSame($this->url . '/foo/', Url::url('/foo/'));
        $this->assertSame('https://foo.com', Url::url('https://foo.com'));
        $this->assertSame('http://bar.com/baz/', Url::url('http://bar.com/baz/'));
        $this->assertSame($this->url . '/bar?hello=world#foo', Url::url('/bar?hello=world#foo'));
    }

    /**
     * Test making an href
     */
    public function testMakeHref()
    {
        $this->assertSame('<a href="#bar">foo</a>', Url::href('#bar', 'foo'));
        $this->assertSame('<a href="http://foo.com" title="bar">foo</a>', Url::href('http://foo.com', 'foo', ['title' => 'bar']));
    }

    /**
     * Get paths to test
     *
     * @return array
     */
    public function getUrlComponents()
    {
        return [
            ['https://', 'foo.com', '/bar', null, null, true, 'https://foo.com/bar/'],
            ['http', 'foo.com', '/bar', null, 'baz', true, 'http://foo.com/bar/#baz'],
            ['http', 'https://bar.com', null, ['baz' => 'foo'], null, false, 'http://bar.com?baz=foo'],
            ['https', '//baz.com', 'foo/bar', ['foo' => 'bar'], null, true, 'https://baz.com/foo/bar/?foo=bar'],
            ['http:', 'url.com', '/baz/', ['foo' => 'bar', 'query' => 'value'], null, true, 'http://url.com/baz/?foo=bar&query=value'],
            ['http://', null, '/foo', null, null, true, 'http://bar.com/foo/'],
            ['http', 'foo.com', '/bar', ['foo' => 'bar'], '#baz', false, 'http://foo.com/bar?foo=bar#baz'],
            ['http', 'foo.com', '/bar/bar baz', null, null, false, 'http://foo.com/bar/bar%20baz'],
            [null, 'foo.com', '//bar//bar baz/file 1.txt', null, null, false, 'http://foo.com/bar/bar%20baz/file%201.txt'],
            ['https:', 'foo.com', '/bar/file.txt', null, null, true, 'https://foo.com/bar/file.txt'],
            [null, 'foo.com', [ 'bar', '/baz' ], null, null, true, 'http://foo.com/bar/baz/'],
        ];
    }
}
