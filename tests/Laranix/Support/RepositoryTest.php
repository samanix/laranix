<?php
namespace Laranix\Tests\Laranix\Support;

use Laranix\Tests\LaranixTestCase;
use Laranix\Support\IO\Repository;

class RepositoryTest extends LaranixTestCase
{
    /**
     * @var array
     */
    protected $values = [
        'foo' => 'bar',
        'bar' => 'baz',
        'null' => null,
        'assoc' => [
            'x' => 'xxx',
            'y' => 'yyy',
        ],
        'array' => [
            'aaa',
            'zzz',
        ],
    ];

    /**
     * Test instance created
     */
    public function testCanCreateInstanceWithSeparator()
    {
        $this->assertSame('-', (new Repository([], '-'))->getSeparator());
    }

    /**
     * Test changing seperator
     */
    public function testCanChangeSeperator()
    {
        $repository = new Repository();

        $this->assertSame('.', $repository->getSeparator());

        $repository->setSeparator('-');

        $this->assertSame('-', $repository->getSeparator());
    }

    /**
     * Test for values
     */
    public function testHasValue()
    {
        $repository = new Repository($this->values);

        $this->assertTrue($repository->has('foo'));
        $this->assertTrue($repository->has('assoc.y'));
        $this->assertTrue($repository->has('array.0'));

        $repository->setSeparator('-');

        $this->assertTrue($repository->has('assoc-x'));
        $this->assertTrue($repository->has('array-1'));
    }

    /**
     * Test value does not exist
     */
    public function testDoesNotHaveValue()
    {
        $repository = new Repository($this->values);

        $this->assertFalse($repository->has('foobar'));
        $this->assertFalse($repository->has('assoc.z'));
        $this->assertFalse($repository->has('array.10'));

        $repository->setSeparator('-');

        $this->assertFalse($repository->has('assoc-r'));
        $this->assertFalse($repository->has('array-10'));
    }

    /**
     * Test get values
     */
    public function testGetValue()
    {
        $repository = new Repository($this->values);

        $this->assertSame($this->values, $repository->all());

        $this->assertSame('bar', $repository->get('foo'));
        $this->assertSame('yyy', $repository->get('assoc.y'));
        $this->assertSame('aaa', $repository->get('array.0'));

        $repository->setSeparator('-');

        $this->assertSame('xxx', $repository->get('assoc-x'));
        $this->assertSame('zzz', $repository->get('array-1'));
    }

    /**
     * Test with bad key seperator
     */
    public function testGetValueWithBadSeparator()
    {
        $repository = new Repository($this->values, '-');

        $this->assertNull($repository->get('associate.x'));
        $this->assertSame('default', $repository->get('associate.y', 'default'));
        $this->assertNull($repository->get('array.100'));
    }

    /**
     * Test getting value with default
     */
    public function testGetValueWithDefault()
    {
        $repository = new Repository($this->values);

        $this->assertSame('default', $repository->get('nothere', 'default'));
        $this->assertTrue($repository->get('nothereeither', true));
        $this->assertSame([], $repository->get('array.100', []));
    }

    /**
     * Test setting value
     */
    public function testSetValue()
    {
        $repository = new Repository($this->values);

        $repository->set('key', 'value');
        $repository->set([
            'key2' => 'value2',
            'key3' => 'value3',
        ]);

        $repository->set('key4.array.foo', 'bar');
        $repository->set('array.2', 'newkey');
        $repository->set('array.3', []);

        $this->assertSame('value', $repository->get('key'));
        $this->assertSame('value2', $repository->get('key2'));
        $this->assertSame('value3', $repository->get('key3'));
        $this->assertSame('bar', $repository->get('key4.array.foo'));
        $this->assertSame('newkey', $repository->get('array.2'));

        $repository->setSeparator('-');

        $this->assertSame('bar', $repository->get('key4-array-foo'));
        $this->assertSame([], $repository->get('array-3'));
    }

    /**
     * Test merge of values
     */
    public function testMergeValues()
    {
        $repository = new Repository($this->values);

        $repository->merge([
            'baz'   => 'value',
            'key9'  => 'value10',
            'foo'   => 'baz',
            'assoc' => [
                'x' => 'yyy',
                'z' => 'xxx',
            ],
            'array' => [
                'new',
                'new2',
            ],
        ]);

        $this->assertSame('value', $repository->get('baz'));
        $this->assertSame(['bar', 'baz'], $repository->get('foo'));
        $this->assertSame('value10', $repository->get('key9'));
        $this->assertSame(['xxx', 'yyy'], $repository->get('assoc.x'));
        $this->assertSame('new', $repository->get('array.2'));

        $repository->setSeparator('-');

        $this->assertSame(['xxx', 'yyy'], $repository->get('assoc-x'));
        $this->assertSame('new2', $repository->get('array-3'));
    }

    /**
     * Test replacement
     */
    public function testReplaceValues()
    {
        $repository = new Repository($this->values);

        $repository->replace([
            'baz'   => 'value',
            'key9'  => 'value10',
            'foo'   => 'baz',
            'assoc' => [
                'x' => 'yyy',
                'z' => 'xxx',
            ],
            'array' => [
                'bbb',
                'ccc',
            ],
        ]);

        $this->assertSame('value', $repository->get('baz'));
        $this->assertSame('baz', $repository->get('foo'));
        $this->assertSame('value10', $repository->get('key9'));
        $this->assertSame('yyy', $repository->get('assoc.x'));
        $this->assertSame('bbb', $repository->get('array.0'));

        $repository->setSeparator('-');

        $this->assertSame('yyy', $repository->get('assoc-x'));
        $this->assertSame('ccc', $repository->get('array-1'));
    }
}
