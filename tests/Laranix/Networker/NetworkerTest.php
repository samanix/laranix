<?php
namespace Tests\Laranix\Networker;

use Laranix\Networker\Networker;
use Laranix\Support\IO\Url\Settings;
use Tests\LaranixTestCase;
use Illuminate\Config\Repository;

class NetworkerTest extends LaranixTestCase
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\Networker\Networker
     */
    protected $networker;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->networker = new Networker(new Repository([
            'networker' => [
                'slugs' => [
                    'facebook'  => 'foo',
                    'twitter'   => 'bar',
                    'instagram' => 'baz',
                    'bitbucket' => 'foobar',
                    'github'    => 'foobaz',
                    'reddit'    => 'bazbar', // /r/ is not required
                ],
                'links' => [
                    'linkedin' => [
                        'url'   => 'https://linkedin.com',
                        'slug'  => 'in/foo',
                    ],
                    'link1' => [
                        'url'   => 'http://foo.com',
                        'slug'  => '/bar',
                    ],
                    'link2' => [
                        'slug'  => 'foo',
                    ],
                ],
            ],
        ]));
    }

    /**
     * Toggle https
     *
     * @param string $state
     */
    protected function toggleHttps(string $state)
    {
        $_SERVER['HTTPS'] = $state;
    }

    /**
     * Test networker creates ok
     */
    public function testCanCreateAppSettings()
    {
        $this->assertInstanceOf(Networker::class, $this->networker);
    }

    /**
     * Test add link
     */
    public function testAddLink()
    {
        $this->assertSame('http://url1.com', $this->networker->add('url1', 'url1.com'));
        $this->assertSame('https://url1.com/bar', $this->networker->add('url1', 'https://url1.com', 'bar'));
        $this->assertSame('http://url1.com/baz', $this->networker->add('url1', 'url1.com/baz'));

        $this->assertSame('https://url2.com/bar', $this->networker->add('url2', 'https://url2.com', 'bar'));
        $this->assertSame('https://url2.com/bar', $this->networker->add('url2', 'https://url2.com/bar/'));

        $this->assertSame('http://url3.com/bar', $this->networker->add('url3', 'http://url3.com', 'bar'));
        $this->assertSame('http://url3.com/bar', $this->networker->add('url3', 'http://url3.com', '/bar/'));

        $this->toggleHttps('on');
        $this->assertSame('https://url4.com', $this->networker->add('url1', 'url4.com'));
        $this->assertSame('https://url4.com/bar', $this->networker->add('url1', 'https://url4.com', 'bar'));
        $this->assertSame('https://url4.com/baz', $this->networker->add('url1', 'url4.com/baz'));
        $this->toggleHttps('off');
    }

    /**
     * Test add link with url settings
     */
    public function testAddLinkWithUrlSettings()
    {
        $this->assertSame('http://url.com/foo', $this->networker->add('ur11', new Settings([ 'domain' => 'url.com', 'path' => 'foo'])));

        $this->assertSame('http://url.com/foo/', $this->networker->add('url2', new Settings([ 'domain' => 'url.com', 'path' => 'foo', 'trailingSlash' => true])));

        $this->assertSame('http://url.com/foo', $this->networker->add('ur13', new Settings([ 'domain' => 'url.com', 'path' => 'foo', 'scheme' => 'http'])));

        $this->assertSame('https://url.com/foo', $this->networker->add('ur14', new Settings([ 'domain' => 'url.com', 'path' => 'foo', 'scheme' => 'https'])));

        $this->toggleHttps('on');
        $this->assertSame(str_replace('http:', 'https:', config('app.url')) . '/baz', $this->networker->add('url5', new Settings([ 'path' => 'baz'])));
        $this->toggleHttps('off');
    }

    /**
     * Test get app version
     */
    public function testGetLink()
    {
        $this->assertNull($this->networker->get('doesnotexist'));
        $this->assertSame('https://facebook.com/foo', $this->networker->get('facebook'));

        $this->assertSame('http://foo.com/bar', $this->networker->get('link1'));

        $this->assertSame(config('app.url') . '/foo', $this->networker->get('link2'));
    }

}
