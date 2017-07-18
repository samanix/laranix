<?php
namespace Laranix\Tests\Laranix\AppSettings;

use Laranix\AppSettings\AppSettings;
use Laranix\Tests\LaranixTestCase;
use Illuminate\Config\Repository;

class AppSettingsTest extends LaranixTestCase
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\AppSettings\AppSettings
     */
    protected $appsettings;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = new Repository([
            'appsettings' => [
                'name'          => 'FooApp',
                'version'       => '1.0.0',
                'success_view'  => 'success',
                'error_view'    => 'error',
                'setting1'      => 'bar',
                'setting2'      => true,
            ],
        ]);

        $this->appsettings = new AppSettings($this->config);
    }


    /**
     * Test app settings creates ok
     */
    public function testCanCreateAppSettings()
    {
        $this->assertInstanceOf(AppSettings::class, $this->appsettings);
    }

    /**
     * Test get app name
     */
    public function testGetName()
    {
        $this->assertSame('FooApp', $this->appsettings->name());
    }

    /**
     * Test get app version
     */
    public function testGetVersion()
    {
        $this->assertSame('1.0.0', $this->appsettings->version(null));
        $this->assertSame('v1.0.0', $this->appsettings->version());
        $this->assertSame('version 1.0.0', $this->appsettings->version('version '));
    }

    /**
     * Test emptying cache
     */
    public function testEmptyCache()
    {
        $this->assertSame('FooApp', $this->appsettings->name());

        $this->config->set('appsettings.name', 'BarApp');

        $this->assertNotSame('BarApp', $this->appsettings->name());

        $this->appsettings->emptyCache();

        $this->assertSame('BarApp', $this->appsettings->name());
    }

    /**
     * Test emptying cache by key
     */
    public function testEmptyCacheByKey()
    {
        $this->assertSame('FooApp', $this->appsettings->name());

        $this->config->set('appsettings.name', 'BarApp');

        $this->assertNotSame('BarApp', $this->appsettings->name());

        $this->appsettings->emptyCache('name');

        $this->assertSame('BarApp', $this->appsettings->name());
    }

    /**
     * Test get setting
     */
    public function testGetSetting()
    {
        $config = $this->config;
        $config->set('appsettings.name', 'BarApp');

        $appsettings = new AppSettings($config);

        $this->assertSame('BarApp', $appsettings->get('name'));
        $this->assertSame('bar', $appsettings->get('setting1'));
        $this->assertTrue($appsettings->get('setting2'));
    }
}
