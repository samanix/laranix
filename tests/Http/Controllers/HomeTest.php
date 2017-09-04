<?php
namespace Laranix\Tests\Http\Controllers;

use Laranix\Tests\LaranixTestCase;
use Laranix\Tests\Http\HasSharedViewVariable;

class HomeTest extends LaranixTestCase
{
    use HasSharedViewVariable;

    /**
     * Test get home/index
     */
    public function testGetHome()
    {
        $response = $this->get('home');

        $response->assertStatus(200);

        $this->assertTrue($this->hasSharedViewVariables('scripts', 'styles', 'images'));
    }

}
