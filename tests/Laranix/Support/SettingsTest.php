<?php
namespace Tests\Laranix\Support;

use Laranix\Support\Exception\LaranixSettingsException;
use Tests\LaranixTestCase;
use Tests\Laranix\Support\Stubs\Settings;

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
     * Test has required
     */
    public function testHasRequired()
    {
        $settings = $this->getSettings();

        $this->assertTrue($settings->hasRequired());

        $settings->setRequired([
            'string'    => 'string',
            'email'     => 'email',
            'int'       => 'int',
            'array'     => 'array',
        ]);

        $this->assertTrue($settings->hasRequired());
    }


    /**
     * Test auto setting required parameters
     */
    public function testAutoSetAllRequired()
    {
        $settings = $this->getSettings();

        $settings->setRequired('all');

        $required = get_object_vars($settings);
        unset($required['required'], $required['requiredTypes'], $required['requiredExcept']);

        $this->assertSame($required, $settings->getRequired());
    }

    /**
     * Test all required set
     */
    public function testHasRequiredSettings()
    {
        $settings = $this->getSettings();

        $settings->setRequiredTypes([
            'string'        => 'string',
            'email'         => 'email',
            'url'           => 'url',
            'int'           => 'int',
            'bool'          => 'bool',
            'array'         => 'array',
            'null'          => 'null',
            'instanceof'    => Settings::class
        ]);

        $this->assertTrue($settings->hasRequired());

        $settings->setRequired(['*']);

        $this->assertTrue($settings->hasRequired());

        $settings->setRequired('all');

        $this->assertTrue($settings->hasRequired());
    }

    /**
     * Test for missing required settings
     */
    public function testThrowsExceptionWhenRequiredValueMissing()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();

        $settings->setRequired('all');

        $settings->hasRequired();
    }

    /**
     * Test for missing required settings
     */
    public function testThrowsExceptionWhenRequiredValueMissingWithStarArray()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();

        $settings->setRequired(['*']);

        $settings->hasRequired();
    }

    /**
     * Test required by type in array
     */
    public function testHasRequiredByRequiredArray()
    {
        $settings = $this->getSettings();

        $settings->setRequired(['string' => 'string', 'email' => 'email', 'url' => 'url', 'instanceof' => Settings::class]);

        $this->assertTrue($settings->hasRequired());
    }

    /**
     * Test by requiredtypes array
     */
    public function testHasRequiredByRequiredTypesArray()
    {
        $settings = $this->getSettings();

        $settings->setRequired(['string', 'email', 'url']);

        $settings->setRequiredTypes(['string' => 'string', 'email' => 'email', 'url' => 'url']);

        $this->assertTrue($settings->hasRequired());
    }

    /**
     * Test has required with exceptions set
     */
    public function testHasRequiredTypeWithExceptions()
    {
        $settings = $this->getSettings();
        $settings->setRequired('all');

        $settings->setRequiredTypes([
            'url'           => 'url',
            'int'           => 'int',
            'array'         => 'array',
            'null'          => 'null',
            'instanceof'    => Settings::class
        ]);

        $settings->setRequiredExcept(['string', 'bool', 'email']);

        $this->assertTrue($settings->hasRequired());
    }

    /**
     * Test has required from mixed sources
     */
    public function testHasRequiredMixedSources()
    {
        $settings = $this->getSettings();
        $settings->setRequired([
            'string',
            'email',
            'int' => 'int',
            'bool',
        ]);

        $settings->setRequiredTypes([
            'bool'      => 'bool',
            'string'    => 'string',
            'email'     => 'email',
        ]);

        $this->assertTrue($settings->hasRequired());
    }

    /**
     * Test setting wrong type for string
     */
    public function testValueDoesNotHaveRequiredTypeString()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();
        $settings->setRequired([
            'string' => 'bool',
        ]);

        $settings->hasRequired();
    }

    /**
     * Test setting wrong type for bool
     */
    public function testValueDoesNotHaveRequiredTypeBool()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();
        $settings->setRequired([
            'bool' => 'int',
        ]);

        $settings->hasRequired();
    }

    /**
     * Test setting wrong type for array
     */
    public function testValueDoesNotHaveRequiredTypeArray()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();
        $settings->setRequired([
            'array' => 'int',
        ]);

        $settings->hasRequired();
    }

    /**
     * Test setting wrong type for url
     */
    public function testValueDoesNotHaveRequiredTypeUrl()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();
        $settings->setRequired([
            'string' => 'url',
        ]);

        $settings->hasRequired();
    }

    /**
     * Test setting wrong type for email
     */
    public function testValueDoesNotHaveRequiredTypeEmail()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings();
        $settings->setRequired([
            'bool' => 'email',
        ]);

        $settings->hasRequired();
    }

    /**
     * Test setting multiple types
     */
    public function testValueAllowedMultipleTypes()
    {
        $settings = $this->getSettings([
            'string'    => [],
            'bool'      => 100,
            'array'     => null,
        ]);

        $settings->setRequired([
            'string'    => 'array|string',
            'bool'      => 'int|string|bool',
            'array'     => 'array|null',
        ]);

        $this->assertTrue($settings->hasRequired());
    }

    /**
     * Test setting multiple types
     */
    public function testValueAllowedMultipleTypesWithInvalidType()
    {
        $this->expectException(LaranixSettingsException::class);

        $settings = $this->getSettings([
            'string'    => [],
            'bool'      => 100,
            'array'     => null,
        ]);

        $settings->setRequired([
            'string'    => 'string|bool|int',
            'bool'      => 'array|string|bool',
            'array'     => 'array|url|email',
        ]);

        $settings->hasRequired();
    }

    /**
     * Get settings
     *
     * @param array|null $options
     * @return \Tests\Laranix\Support\Stubs\Settings
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
        ], $options));
    }
}
