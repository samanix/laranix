<?php
namespace Tests\Http\Controllers;

use Tests\LaranixTestCase;

class HomeTest extends LaranixTestCase
{
    /**
     * Test get home/index
     */
    public function testGetHome()
    {
        $response = $this->get('home');

        $response->assertStatus(200);
    }

}
