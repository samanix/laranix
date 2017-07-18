<?php
namespace Laranix\Tests\Laranix\Support;

use Laranix\Tests\LaranixTestCase;

class HelpersTest extends LaranixTestCase
{
    /**
     * Test model diff returns correct differences
     */
    public function testModelDiffReturnsCorrect()
    {
        $old = [
            'foo'   => 'bar',
            'bar'   => 'baz',
            'baz'   => 'foo',
            'obj'   => new \stdClass(),
            'arr'   => ['foo' => 'bar', 'baz' => ['test']],
            'null'  => 'notnull',
            'created_at' => 123,
            'updated_at' => 123,
        ];

        $new = [
            'foo'   => 'baz',
            'bar'   => 'foo',
            'baz'   => 'foo',
            'obj'   => ['foo' => 'bar', 'baz' => ['test']],
            'arr'   => ['foo' => 'bar', 'baz' => ['test']],
            'null'  => null,
            'extra' => 100,
            'created_at' => 456,
            'updated_at' => 789,
        ];

        $expected = [
            'foo'   => ['bar', 'baz'],
            'bar'   => ['baz', 'foo'],
            'obj'   => [json_encode(new \stdClass()), json_encode(['foo' => 'bar', 'baz' => ['test']])],
            'null'  => ['notnull', null],
            'extra' => [null, 100],
        ];

        $expected2 = [
            'bar'           => ['baz', 'foo'],
            'obj'           => [json_encode(new \stdClass()), json_encode(['foo' => 'bar', 'baz' => ['test']])],
            'null'          => ['notnull', null],
            'created_at'    => [123, 456],
            'updated_at'    => [123, 789]
        ];

        $this->assertSame(json_encode($expected, true), model_diff($old, $new));
        $this->assertSame($expected, model_diff($old, $new, false));
        $this->assertSame(json_encode($expected2, true), model_diff($old, $new, true, ['foo', 'extra']));
    }

}
