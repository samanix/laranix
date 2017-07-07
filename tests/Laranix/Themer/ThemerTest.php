<?php
namespace Tests\Laranix\Themer;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Themer\Themer;
use Laranix\Themer\ThemeRepository;
use Tests\LaranixTestCase;
use Mockery as m;
use Tests\Laranix\Themes\Stubs\Themes;

class ThemerTest extends LaranixTestCase
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = new Repository(Themes::$themes);
    }

    /**
     * Test theme setting
     */
    public function testSetTheme()
    {
        $themer = $this->createThemer();

        $this->assertSame('foo', $themer->setTheme()->getKey());
        $this->assertSame('bar', $themer->setTheme('bar')->getKey());
        $this->assertSame('foo', $themer->setTheme('doesntexist')->getKey());
        $this->assertSame('foo', $themer->setTheme('alsodoesntexist', true)->getKey());
    }

    /**
     * Test getting theme
     */
    public function testGetTheme()
    {
        $themer = $this->createThemer();

        $this->assertSame('foo', $themer->getTheme()->getKey());
        $this->assertSame('bar', $themer->getTheme('bar')->getKey());
        $this->assertSame('foo', $themer->getTheme('doesntexist')->getKey());

        $this->assertSame('foo', $themer->getDefaultTheme()->getKey());
    }

    /**
     * Test checking if theme is default
     */
    public function testCheckIfThemeIsDefault()
    {
        $themer = $this->createThemer();

        $this->assertTrue($themer->themeIsDefault());
        $this->assertTrue($themer->themeIsDefault('foo'));
        $this->assertTrue($themer->themeIsDefault($themer->getDefaultTheme()));
        $this->assertTrue($themer->themeIsDefault($themer->getDefaultTheme()->getKey()));

        $this->assertFalse($themer->themeIsDefault('bar'));
        $this->assertFalse($themer->themeIsDefault($themer->getTheme('bar')));
        $this->assertFalse($themer->themeIsDefault($themer->getTheme('baz')->getKey()));
    }

    /**
     * Test user override
     */
    public function testGetThemeUserOverride()
    {
        $themer = $this->createThemer(true);

        $this->assertSame('bar', $themer->getTheme()->getName());
    }

    /**
     * Test override theme
     */
    public function testGetThemeOverride()
    {
        $this->config->set('themer.themes.bar.override', true);

        $themer = $this->createThemer();

        $this->assertSame('bar', $themer->getTheme()->getName());
    }

    /**
     * @param bool $cookieReturn
     * @return \Laranix\Themer\Themer
     */
    protected function createThemer($cookieReturn = false)
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('hasCookie')->andReturn($cookieReturn);
        $request->shouldReceive('cookie')->andReturn('bar');

        return new Themer($this->config, $request, new ThemeRepository($this->config));
    }
}
