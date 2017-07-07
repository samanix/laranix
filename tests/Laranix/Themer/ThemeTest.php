<?php
namespace Tests\Laranix\Themer;

use Laranix\Support\IO\Url\Url;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemeSettings;
use Tests\LaranixTestCase;

class ThemeTest extends LaranixTestCase
{
    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $_SERVER['HTTP_HOST'] = 'foo.com';
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($_SERVER['HTTP_HOST']);
    }

    /**
     * Test get settings
     */
    public function testGetSetting()
    {
        $theme = $this->createTheme();

        $this->assertSame('foo', $theme->getKey());
        $this->assertSame('bar', $theme->getName());
        $this->assertSame(public_path('theme'), $theme->getPath());
        $this->assertSame(Url::to('/url/to/theme'), $theme->getWebPath());

        $this->assertTrue($theme->getSetting('enabled'));
        $this->assertFalse($theme->getSetting('override'));
    }

    /**
     * Test getting relative paths
     *
     * @dataProvider getRelativePaths
     * @param $paths
     * @param $pathExpect
     */
    public function testGetRelativePaths($paths, $pathExpect)
    {
        $theme = $this->createTheme($paths);

        $this->assertSame(public_path($pathExpect), $theme->getPath());

        $this->assertSame(Url::to($paths['webPath']), $theme->getWebPath());
    }

    /**
     * Test getting absolute paths
     *
     * @dataProvider getAbsolutePaths
     * @param $paths
     * @param $pathExpect
     * @param $webPathExpect
     */
    public function testGetAbsolutePaths($paths, $pathExpect, $webPathExpect)
    {
        $theme = $this->createTheme($paths);

        $this->assertSame($pathExpect, $theme->getPath());

        $this->assertSame($webPathExpect, $theme->getWebPath());
    }

    /**
     * Test verification
     */
    public function testGetVerified()
    {
        $this->assertTrue($this->createTheme(['path' => __DIR__])->verified());
        $this->assertFalse($this->createTheme(['path' => 'random/path'])->verified());
    }

    /**
     * Get bad theme settings
     *
     * @return array
     */
    public function getBadThemeSettings()
    {
        return [
            [['name' => 'bar', 'path' => 'path/to/theme', 'webPath'   => 'foo.com/theme',]],
            [['key' => 'foo', 'path' => 'path/to/theme/', 'webPath'   => 'foo.com/theme',]],
            [['name' => 'bar']],
        ];
    }

    /**
     * @return array
     */
    public function getRelativePaths()
    {
        return [
            [['path' => '/foo', 'webPath' => 'bar/'], 'foo'],
            [['path' => 'bar/baz/', 'webPath' => '/theme/baz/bar/'], 'bar/baz'],
        ];
    }

    /**
     * @return array
     */
    public function getAbsolutePaths()
    {
        return [
            [['path' => __DIR__, 'webPath' => 'http://foo.com/theme/'], __DIR__, 'http://foo.com/theme'],
            [['path' => __DIR__ . '/../', 'webPath' => '//www.url.com/foo/theme'], dirname(__DIR__, 1), '//www.url.com/foo/theme'],
        ];
    }

    /**
     * Create fresh theme for test
     *
     * @param array $options
     * @return \Laranix\Themer\Theme
     */
    protected function createTheme(array $options = [])
    {
        return new Theme(new ThemeSettings(array_replace([
                                    'key'       => 'foo',
                                    'name'      => 'bar',
                                    'path'      => 'theme',
                                    'webPath'   => 'url/to/theme',
                                ], $options)));
    }
}
