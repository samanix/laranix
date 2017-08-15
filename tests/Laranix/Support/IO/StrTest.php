<?php
namespace Laranix\Tests\Laranix\Support\IO;

use Laranix\Support\IO\Str\Settings;
use Laranix\Tests\LaranixTestCase;
use Laranix\Support\IO\Str\Str;

class StrTest extends LaranixTestCase
{
    /**
     * Test string formatting
     */
    public function testStrFormatting()
    {
        $this->assertSame('hello world',
                          Str::format('{{foo}}   {{bar}}', ['foo' => 'hello', 'bar' => 'world']));

        $this->assertSame('helloworld',
                          Str::format('#foo##bar#', ['foo' => 'hello', 'bar' => 'world'],
                                     ['leftSeparator' => '#', 'rightSeparator' => '#']));

        $this->assertSame('hello_world',
                          Str::format('#foo#_#bar# ', ['foo' => 'hello', 'bar' => 'world'],
                                      new Settings([ 'leftSeparator' => '#', 'rightSeparator' => '#'])));

        $this->assertSame('no keys {{here}}',
                          Str::format('no  keys {{here}}  ', []));

        $this->assertSame('no empty keys',
                          Str::format(' no empty keys {{here}}', [],
                                      new Settings([ 'removeUnparsed' => true])));

        $this->assertSame('different replacement --REMOVED--',
                          Str::format('different replacement {{here}}', [],
                                      ['removeUnparsed' => true, 'unparsedReplacement' => '--REMOVED--']));


        $this->assertSame('no empty  keys',
                          Str::format('no empty  keys {{here}}', [],
                                      new Settings(['removeUnparsed' => true, 'removeExtraSpaces' => false])));
    }
}
