<?php
namespace Laranix\Tests\Http\Controllers;

use Laranix\Tests\LaranixTestCase;

class HomeTest extends LaranixTestCase
{
    /**
     * Test get home/index
     */
    public function testGetHome()
    {
        $response = $this->get('home');

        $response->assertStatus(200);
        $response->assertViewHas(['scripts', 'styles', 'images']);
    }

}
