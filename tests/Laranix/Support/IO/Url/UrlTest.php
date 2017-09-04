<?php
namespace Laranix\Tests\Laranix\Support\IO;

use Laranix\Support\IO\Url\Url;
use Laranix\Tests\LaranixTestCase;

class UrlTest extends LaranixTestCase
{
    /**
     * @var \Laranix\Support\IO\Url\Url
     */
    protected $url;

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

        $this->url = new Url($this->baseurl);

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
     * Test url creation
     *
     * @dataProvider getUrlComponents
     * @param $a
     * @param $b
     * @param $c
     * @param $d
     * @param $e
     * @param $f
     * @param $g
     */
    public function testCreateUrl($a, $b, $c, $d, $e, $f, $g)
    {
        $this->assertSame($g, $this->url->create($a, $b, $c, $d, $e, $f));
    }

    /**
     * Test simple creation
     */
    public function testCreateToUrl()
    {
        $this->assertSame($this->baseurl . '/bar', $this->url->to('bar'));
        $this->assertSame($this->baseurl . '/bar/baz', $this->url->to(['bar', 'baz']));
        $this->assertSame($this->baseurl, $this->url->to(null));
        $this->assertSame($this->baseurl . '/bar/', $this->url->to('bar', null, null, true));
        $this->assertSame($this->baseurl . '/', $this->url->to(null, null, null, true));
        $this->assertSame($this->baseurl . '#bar', $this->url->to(null, null, 'bar'));
        $this->assertSame($this->baseurl . '/bar/?hello=world', $this->url->to('bar', [ 'hello' => 'world'], null, true));
        $this->assertSame($this->baseurl . '/bar?hello=world#foo', $this->url->to('bar', [ 'hello' => 'world'], 'foo', false));

        $this->assertSame($this->baseurl, $this->url->self());
    }

    /**
     * Test making url
     */
    public function testMakeUrl()
    {
        $this->assertSame($this->baseurl . '/foo', $this->url->url('/foo'));
        $this->assertSame($this->baseurl . '/foo/', $this->url->url('/foo/'));
        $this->assertSame('https://foo.com', $this->url->url('https://foo.com'));
        $this->assertSame('http://bar.com/baz/', $this->url->url('http://bar.com/baz/'));
        $this->assertSame($this->baseurl . '/bar?hello=world#foo', $this->url->url('/bar?hello=world#foo'));
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
            ['http://', null, '/foo', null, null, true, 'http://homestead.app/foo/'],
            ['http', 'foo.com', '/bar', ['foo' => 'bar'], '#baz', false, 'http://foo.com/bar?foo=bar#baz'],
            ['http', 'foo.com', '/bar/bar baz', null, null, false, 'http://foo.com/bar/bar%20baz'],
            [null, 'foo.com', '//bar//bar baz/file 1.txt', null, null, false, 'http://foo.com/bar/bar%20baz/file%201.txt'],
            ['https:', 'foo.com', '/bar/file.txt', null, null, true, 'https://foo.com/bar/file.txt'],
            [null, 'foo.com', [ 'bar', '/baz' ], null, null, true, 'http://foo.com/bar/baz/'],
        ];
    }
}
