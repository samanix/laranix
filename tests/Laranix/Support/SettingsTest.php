<?php
namespace Laranix\Tests\Laranix\Support;

use Laranix\Support\Exception\LaranixSettingsException;
use Laranix\Tests\LaranixTestCase;
use Laranix\Tests\Laranix\Support\Stubs\Settings;

class SettingsTest extends LaranixTestCase
{
    /**
     * Test set attributes
     */
    public function testSetAttributes()
    {
        $settings = $this->getSettings();

        $this->assertSame('string', $settings->string);
        $this->assertSame('email@email.com', $settings->email);
        $this->assertSame(100, $settings->int);
        $this->assertSame([], $settings->array);
    }

    /**
     * Test has required settings
     */
    public function testHasRequiredSettings()
    {
        $settings = $this->getSettings();

        $this->assertTrue($settings->hasRequiredSettings());

        $settings->setRequiredProperties([
            'string'    => 'string',
            'email'     => 'email',
            'int'       => 'int',
            'array'     => 'array',
        ]);

        $this->assertTrue($settings->hasRequiredSettings());

        $settings->setRequiredProperties(['*']);

        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test setting all parameters required
     */
    public function testSetAllPropertiesRequired()
    {
        $settings = $this->getSettings();

        $settings->setRequiredProperties(['*']);

        $required = get_object_vars($settings);
        unset($required['required'],
              $required['requiredTypes'],
              $required['ignored'],
              $required['requiredParsed'],
              $required['allRequired']);

        $this->assertSame($required, $settings->getRequiredProperties());
    }

    /**
     * Test for missing required settings
     */
    public function testThrowsExceptionWhenRequiredValueHasBadType()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();

        $settings->string = null;

        $settings->setRequiredProperties(['*']);

        $settings->setRequiredPropertyTypes('string', 'string');

        $settings->hasRequiredSettings();
    }

    /**
     * Test required by type
     */
    public function testHasRequiredByRequiredTypeArray()
    {
        $settings = $this->getSettings();

        $settings->setRequiredTypes([
            'string'        => 'string',
            'email'         => 'email',
            'url'           => 'url',
            'instanceof'    => Settings::class
        ]);

        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test by required types array
     */
    public function testHasRequiredByRequiredTypesArray()
    {
        $settings = $this->getSettings();

        $settings->setRequiredProperties(['string', 'email', 'url']);

        $settings->setRequiredTypes([
            'string'    => 'string',
            'email'     => 'email',
            'url'       => 'url'
        ]);

        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test has required with ignored properties set
     */
    public function testHasRequiredTypeWithIgnored()
    {
        $settings = $this->getSettings();
        $settings->setRequiredProperties(['*']);

        $settings->setRequiredTypes([
            'url'           => 'url',
            'int'           => 'int',
            'array'         => 'array',
            'null'          => 'null',
            'instanceof'    => Settings::class,
            'string'        => 'any',
        ]);

        $settings->setIgnoredProperties([
            'string',
            'bool',
            'email'
        ]);

        $this->assertSame(['string', 'bool', 'email'], $settings->getIgnoredProperties());
        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test has required types from mixed sources
     */
    public function testHasRequiredWithMixedSourceTypes()
    {
        $settings = $this->getSettings();

        $settings->setRequiredProperties([
            'string',
            'email',
            'int' => 'int',
            'bool',
            'optional',
        ]);

        $settings->setRequiredTypes([
            'bool'      => 'bool',
            'string'    => 'string',
            'email'     => 'email',
            'optional'  => 'null',
        ]);

        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test setting wrong type for string
     */
    public function testPropertyDoesNotHaveValidType()
    {
        $this->expectException(LaranixSettingsException::class);
        $this->expectExceptionMessage("Expected 'bool' for property 'string' in Laranix\Tests\Laranix\Support\Stubs\Settings, got 'string'");

        $settings = $this->getSettings();

        $settings->setRequiredProperties([
            'string' => 'bool',
        ])->hasRequiredSettings();

        $settings->getParsedRequiredProperties();
    }

    /**
     * Test setting multiple allowed types
     */
    public function testPropertyAllowedMultipleTypes()
    {
        $settings = $this->getSettings([
            'string'    => [],
            'bool'      => 100,
            'array'     => null,
        ]);

        $settings->setRequiredTypes([
            'string'    => 'array|string',
            'bool'      => 'int|string|bool',
            'array'     => 'array|null',
        ]);

        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test setting multiple types
     */
    public function testValueAllowedMultipleTypesWithInvalidType()
    {
        $this->expectException(LaranixSettingsException::class);
        $this->expectExceptionMessage("Expected 'string|bool|int' for property 'string' in Laranix\Tests\Laranix\Support\Stubs\Settings, got 'array'");

        $settings = $this->getSettings([
            'string'    => [],
            'bool'      => 100,
            'array'     => null,
        ]);

        $settings->setRequiredTypes([
            'string'    => 'string|bool|int',
            'bool'      => 'array|string|bool',
            'array'     => 'array|url|email',
        ]);

        $settings->hasRequiredSettings();
    }

     /**
     * Test setting optional setting
     */
    public function testOptionalPropertyValue()
    {
        $settings = $this->getSettings([
            'optional'  => 'this is a string',
        ]);

        $settings->setRequiredPropertyTypes('optional', 'optional|string');

        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test setting optional setting with no value
     */
    public function testOptionalPropertyValueWithNoValue()
    {
        $settings = $this->getSettings();

        $settings->setRequiredPropertyTypes('optional', 'optional|null');

        $this->assertTrue($settings->hasRequiredSettings());
    }

    /**
     * Test setting optional setting
     */
    public function testOptionalSettingBadType()
    {
        $this->expectException(LaranixSettingsException::class);
        $this->expectExceptionMessage("Expected 'array' for optional property 'optional' in Laranix\Tests\Laranix\Support\Stubs\Settings, got 'string'");

        $settings = $this->getSettings([
            'optional'  => 'this is a string',
        ]);

        $settings->setRequiredPropertyTypes('optional', 'optional|array');

        $settings->hasRequiredSettings();
    }

    /**
     * Get settings
     *
     * @param array|null $options
     * @return \Laranix\Tests\Laranix\Support\Stubs\Settings
     */
    protected function getSettings(?array $options = [])
    {
        return new Settings(array_replace([
            'string'        => 'string',
            'email'         => 'email@email.com',
            'url'           => 'http://foo.com',
            'int'           => 100,
            'bool'          => true,
            'array'         => [],
            'null'          => null,
            'instanceof'    => new Settings(),
            'optional'      => null,
        ], $options));
    }
}
