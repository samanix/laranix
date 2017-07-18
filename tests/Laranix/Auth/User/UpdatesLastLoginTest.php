<?php
namespace Laranix\Tests\Laranix\Auth\User;

use Carbon\Carbon;
use Laranix\Auth\User\UpdatesLastLogin;
use Laranix\Support\Exception\NullValueException;
use Laranix\Tests\LaranixTestCase;

class UpdatesLastLoginTest extends LaranixTestCase
{
    /**
     * Test creating group
     */
    public function testGetLastLoginKey()
    {
        $this->assertSame('last_login', $this->createLastLoginProvider(Carbon::now())->getLastLoginKey());
    }

    /**
     * Test get last login
     */
    public function testGetLastLogin()
    {
        $d1 = $this->createLastLoginProvider(Carbon::create(2017, 1, 1, 12, 0, 0))->getLastLogin();
        $d2 = $this->createLastLoginProvider(Carbon::create(2020, 12, 25, 8, 55, 30))->getLastLogin();

        $this->assertSame('2017-01-01 12:00:00', $d1->toDateTimeString());

        $this->assertSame('2020-12-25 08:55:30', $d2->toDateTimeString());
    }

    /**
     * Test get last login with no property set
     */
    public function testGetLastLoginWithNoPropertySet()
    {
        $mocks = [
            'asDateTime',
            'getAttributeFromArray'
        ];

        $date = $this->getMockForTrait(UpdatesLastLogin::class, [], '', true, true, true, $mocks, true);
        $date->last_login = null;

        $date->method('asDateTime')->will($this->returnCallback([$this, 'asDateTime']));

        $date->method('getAttributeFromArray')->will($this->returnCallback(function() {
            return null;
        }));

        $last = $date->getLastLogin();

        $this->assertSame(Carbon::now()->toDateTimeString(), $last->toDateTimeString());
    }

    /**
     * Test updating last login with no property set
     */
    public function testUpdateLastLoginWithNoPropertySet()
    {
        $this->expectException(NullValueException::class);

        $mocks = [
            'asDateTime',
            'getAttributeFromArray'
        ];

        $date = $this->getMockForTrait(UpdatesLastLogin::class, [], '', true, true, true, $mocks, true);
        $date->last_login = Carbon::create(2005, 1, 1, 12, 0, 0)->toDateTimeString();

        $date->method('asDateTime')->will($this->returnCallback([$this, 'asDateTime']));

        $date->method('getAttributeFromArray')->will($this->returnCallback(function() {
            return null;
        }));

        $date->updateLastLogin();
    }

    /**
     * Test updating last login
     */
    public function testUpdateLastLogin()
    {
        $d1 = $this->createLastLoginProvider(Carbon::now()->subWeek(1));

        $d1->updateLastLogin();

        $this->assertSame(Carbon::now()->toDateTimeString(), $d1->getLastLogin()->toDateTimeString());
    }

    /**
     * Test updating last login with custom override
     */
    public function testUpdateLastLoginWithCustomOverride()
    {
        $d1 = $this->createLastLoginProvider(Carbon::now());

        $d1->updateLastLogin(Carbon::create(2000, 10, 10, 16, 0, 0));

        $this->assertSame('2000-10-10 16:00:00', $d1->getLastLogin()->toDateTimeString());
    }

    /**
     * Test get last login after updating value
     */
    public function testGetLastLoginAfterUpdating()
    {
        $d1 = $this->createLastLoginProvider(Carbon::create(2000, 1, 1, 0, 0, 0));

        $this->assertSame('2000-01-01 00:00:00', $d1->getLastLogin()->toDateTimeString());

        $d1->updateLastLogin(Carbon::create(2010, 1, 1, 0, 0, 0));

        $this->assertSame('2010-01-01 00:00:00', $d1->getLastLogin()->toDateTimeString());
    }

    /**
     * Create token mock
     *
     * @param \Carbon\Carbon $carbon
     * @return \Laranix\Auth\User\UpdatesLastLogin|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createLastLoginProvider(Carbon $carbon)
    {
        $mocks = [
            'asDateTime',
            'getAttributeFromArray'
        ];

        $date = $this->getMockForTrait(UpdatesLastLogin::class, [], '', true, true, true, $mocks, true);
        $date->last_login = $carbon->toDateTimeString();

        $date->method('asDateTime')->will($this->returnCallback([$this, 'asDateTime']));

        $date->method('getAttributeFromArray')->will($this->returnCallback(function() use ($date) {
            return $date->last_login;
        }));

        return $date;
    }

    /**
     * @return mixed
     */
    public function asDateTime()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', func_get_args()[0]);
    }

}
