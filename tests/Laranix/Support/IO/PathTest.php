<?php
namespace Laranix\Tests\Laranix\Support\IO;

use Laranix\Support\IO\Path;
use Laranix\Tests\LaranixTestCase;

class PathTest extends LaranixTestCase
{
    /**
     * @dataProvider paths
     * @param $args
     */
    public function testPathCombine($args, $expected)
    {
        $this->assertSame($expected, call_user_func_array([Path::class, 'combine'], $args));
    }

    /**
     * @return array
     */
    public function paths()
    {
        return [
            [['foo', 'bar', 'baz'], 'foo/bar/baz'],
            [['/baz', 'bar', '/foo'], '/baz/bar/foo'],
            [['/baz', 'bar', '/foo/'], '/baz/bar/foo/'],
            [['baz/', '/bar/', '/foo/'], 'baz/bar/foo/'],
            [[__DIR__, '../IO/', 'PathTest.php'], realpath(__DIR__ . '/../IO/PathTest.php')],
            [[__DIR__, '../', 'IO'], realpath(__DIR__ . '/../IO')],
            [[__DIR__, '..\\', 'IO\\PathTest.php'], realpath(__DIR__ . '/../IO/PathTest.php')],
            [[__DIR__, '..\\', 'IO\\'], realpath(__DIR__ . '/../IO')],
            [['\\baz', 'bar', '\\foo'], '/baz/bar/foo'],
            [[], null],
            [[null], null],
            [[null, null], null],
            [['', ''], null],
            [['0', 'path', 0], '0/path/0'],
        ];
    }
}
