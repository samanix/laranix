<?php
namespace Tests\Laranix\Themer;

use Illuminate\Config\Repository;
use Laranix\Support\Exception\KeyExistsException;
use Laranix\Support\Exception\NullValueException;
use Laranix\Themer\Theme;
use Laranix\Themer\ThemeRepository;
use Laranix\Themer\ThemeSettings;
use Tests\Laranix\Themes\Stubs\Themes;
use Tests\LaranixTestCase;

class ThemeRepositoryTest extends LaranixTestCase
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\Themer\ThemeRepository
     */
    protected $repository;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = new Repository(Themes::$themes);

        $this->repository = new ThemeRepository($this->config);
    }

    /**
     * Test themes have auto loaded
     */
    public function testThemesLoaded()
    {
        $this->assertSame('bar', $this->repository->get('bar')->getKey());
        $this->assertSame('foo', $this->repository->getDefault()->getKey());
    }

    /**
     * Test theme setting
     */
    public function testCreateWithoutLoadingThemes()
    {
        $this->expectException(\InvalidArgumentException::class);

        $repo = new ThemeRepository($this->config, false);
        $repo->get('nokeyhere');
    }

    /**
     * Test theme setting
     */
    public function testCreateWithoutLoadThemesAndNoDefaultFallback()
    {
        $this->expectException(\InvalidArgumentException::class);

        $repo = new ThemeRepository($this->config, false);
        $repo->get('anyotherkey', false);
    }

    /**
     * Test array returned for all themes
     */
    public function testGetAllThemes()
    {
        $this->assertTrue(is_array($this->repository->all()));
    }

    /**
     * Test adding theme
     */
    public function testAddTheme()
    {
        $this->assertInstanceOf(Theme::class, $this->repository->add($this->getSettings(['key' => 'newfoo'])));

        $this->assertSame('newfoo', $this->repository->get('newfoo')->getKey());
    }

    /**
     * Test adding with key as second param
     */
    public function testAddThemeWithManuallySetKey()
    {
        $this->assertSame('otherkey', $this->repository->add($this->getSettings(), 'otherkey')->getKey());
    }

    /**
     * Test adding existing theme
     */
    public function testAddThemeThatExists()
    {
        $this->expectException(KeyExistsException::class);

        $this->repository->add($this->getSettings());
    }

    /**
     * Test adding disabled theme
     */
    public function testAddDisabledTheme()
    {
        $this->assertNull($this->repository->add($this->getSettings(['key' => 'disabledfoo', 'enabled' => false])));
    }

    /**
     * Test adding a default theme
     */
    public function testAddDefaultTheme()
    {
        $this->assertSame('foo', $this->repository->getDefault()->getKey());
    }

    /**
     * Test adding default theme by fallback
     */
    public function testAddDefaultThemeWithNoDefaultSet()
    {
        $repo = new ThemeRepository(new Repository(['themer' => [
                'themes' => [
                    'default-by-default' => [
                        'name'      => 'foo',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/foo',
                    ],
                    'foo' => [
                        'name'      => 'foo',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/foo',
                    ],
                    'bar' => [
                        'name'      => 'bar',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/bar',
                    ],
                    'baz' => [
                        'name'      => 'foo',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/foo',
                    ],
                ],
            ]]));

        $this->assertSame('default-by-default', $repo->getDefault()->getKey());
    }

    /**
     * Test adding default with default set
     */
    public function testAddDefaultWithDefaultSet()
    {
        $this->repository->add($this->getSettings(['key' => 'foobarnew', 'default' => true]));

        $this->assertSame('foo', $this->repository->getDefault()->getKey());
    }

    /**
     * Test adding override
     */
    public function testAddOverride()
    {
        $repo = new ThemeRepository(new Repository(['themer' => [
                'themes' => [
                    'foo' => [
                        'name'      => 'foo',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/foo',
                        'default'   => true,
                    ],
                    'bar' => [
                        'name'      => 'bar',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/bar',
                        'override'  => true,
                    ],
                    'baz' => [
                        'name'      => 'foo',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/foo',
                    ],
                ],
            ]]));

        $this->assertSame('bar', $repo->getOverride()->getKey());
    }

    /**
     * Test adding override when already set
     */
    public function testAddOverrideWhenOverrideIsSet()
    {
        $repo = new ThemeRepository(new Repository(['themer' => [
                'themes' => [
                    'foo' => [
                        'name'      => 'foo',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/foo',
                    ],
                    'bar' => [
                        'name'      => 'bar',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/bar',
                        'override'  => true,
                    ],
                    'baz' => [
                        'name'      => 'foo',
                        'path'      => __DIR__,
                        'webPath'   => 'themes/foo',
                        'default'   => true,
                    ],
                ],
            ]]));

        $repo->add($this->getSettings(['key' => 'newfoo', 'override' => true]));

        $this->assertSame('bar', $repo->getOverride()->getKey());
    }

    /**
     * Test getting theme
     */
    public function testGetTheme()
    {
        $this->assertSame('bar', $this->repository->get('bar')->getKey());
        $this->assertSame('foo', $this->repository->get('this-key-wont-exist')->getKey());
    }

     /**
     * Test getting theme that doesn't exist
     */
    public function testGettingNonExistentTheme()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repository->get('this-key-wont-exist', false);
    }

    /**
     * Test for having theme
     */
    public function testHasTheme()
    {
        $this->assertTrue($this->repository->has('foo'));
        $this->assertTrue($this->repository->has('baz'));

        $this->assertFalse($this->repository->has('no-key'));
        $this->assertFalse($this->repository->has(null));
    }

     /**
     * Test setting theme as default
     */
    public function testGetSetDefaultTheme()
    {
        $this->assertSame('foo', $this->repository->getDefault()->getKey());

        $this->repository->setDefault('bar');

        $this->assertSame('bar', $this->repository->getDefault()->getKey());

        $this->repository->setDefault($this->repository->get('foo'));

        $this->assertSame('foo', $this->repository->getDefault()->getKey());
    }

     /**
     * Test setting non existent theme as default
     */
    public function testSetNullDefaultTheme()
    {
        $this->expectException(NullValueException::class);

        $this->repository->setDefault('nulltheme');
    }

    /**
     * Test setting unverified theme as default
     */
    public function testSetUnverifiedDefaultTheme()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repository->setDefault('baz');
    }

    /**
     * Test if override set
     */
    public function testHasOverride()
    {
        $this->assertFalse($this->repository->hasOverride());

        $this->repository->add($this->getSettings(['key' => 'override', 'override' => true]));

        $this->assertTrue($this->repository->hasOverride());

        $this->repository->setOverride(null);

        $this->assertFalse($this->repository->hasOverride());
    }

    /**
     * Test get/set override
     */
    public function testGetSetOverride()
    {
        $this->assertNull($this->repository->getOverride());

        $this->repository->add($this->getSettings(['key' => 'override', 'override' => true]));

        $this->assertSame('override', $this->repository->getOverride()->getKey());

        $this->repository->setOverride('foo');

        $this->assertSame('foo', $this->repository->getOverride()->getKey());
    }

    /**
     * Test set override when theme is not in theme list
     */
    public function testSetOverrideReturnsDefaultThemeWhenThemeNotAdded()
    {
        $this->repository->setOverride('notheme');
        $this->assertSame('foo', $this->repository->getOverride()->getKey());
    }

    /**
     * Test setting override with unverified theme
     */
    public function testSetOverrideReturnsDefaultThemeWhenThemeNotVerified()
    {
        $this->repository->add($this->getSettings(['key' => 'notverified', 'path' => '/not/a/path']));

        $this->repository->setOverride('notverified');

        $this->assertSame('foo', $this->repository->getOverride()->getKey());
    }

    /**
     * Get theme settings
     *
     * @param array $settings
     * @return \Laranix\Themer\ThemeSettings
     */
    protected function getSettings(array $settings = [])
    {
        return new ThemeSettings(array_replace([
            'key'       => 'foo',
            'name'      => 'bar',
            'path'      => __DIR__,
            'webPath'   => 'foo.com/bar',
        ], $settings));
    }
}
