<?php
namespace Laranix\Tests\Laranix\AntiSpam;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\RedirectResponse;
use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\AntiSpam\Recaptcha\Recaptcha;
use Laranix\Tests\LaranixTestCase;

class RecaptchaTest extends LaranixTestCase
{
    /**
     * Test callback script returned matches config
     */
    public function testGetCallbackScript()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $this->assertSame('fooscript', (new Recaptcha($config, $request, $view, $guzzle))->getCallbackScript());
    }

    /**
     * Test when recaptcha disabled
     */
    public function testWhenEnabledIsFalse()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs(false);

        $recaptcha = new Recaptcha($config, $request, $view, $guzzle);

        $this->assertFalse($recaptcha->enabled());
        $this->assertNull($recaptcha->render());
        $this->assertTrue($recaptcha->verify());
        $this->assertNull($recaptcha->redirect());
    }

    /**
     * Test when recaptcha disabled due to environment
     */
    public function testWhenEnvironmentIsDisabled()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs(true, 'disabled1');

        $recaptcha = new Recaptcha($config, $request, $view, $guzzle);

        $this->assertFalse($recaptcha->enabled());
        $this->assertNull($recaptcha->render());
        $this->assertTrue($recaptcha->verify());
        $this->assertNull($recaptcha->redirect());
    }

    /**
     * Test when recaptcha disabled for users
     */
    public function testIsNotEnabledWhenNotEnabledForUsers()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs(true, 'testing', true);

        $request->shouldReceive('user')->andReturn(1);

        $this->assertFalse((new Recaptcha($config, $request, $view, $guzzle))->enabled());
    }

    /**
     * Test when recaptcha disabled for users
     */
    public function testIsEnabledWhenEnabledForUsers()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $request->shouldReceive('user')->andReturn(1);

        $this->assertTrue((new Recaptcha($config, $request, $view, $guzzle))->enabled());
    }

    /**
     * Test when recaptcha is enabled for guests
     */
    public function testIsEnabledWhenEnabledForUsersAndIsGuest()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $request->shouldReceive('user')->andReturnNull();

        $this->assertTrue((new Recaptcha($config, $request, $view, $guzzle))->enabled());
    }

    /**
     * Test render when recaptcha enabled
     */
    public function testRenderWhenEnabled()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $view->shouldReceive('exists')->andReturn(true);
        $view->shouldReceive('make')->andReturnSelf();
        $view->shouldReceive('render')->andReturn('output');

        $this->assertSame('output', (new Recaptcha($config, $request, $view, $guzzle))->render());
    }

    /**
     * Test when view not found
     */
    public function testRenderThrowsExceptionWhenViewNotFound()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $view->shouldReceive('exists')->andReturn(false);
        $view->shouldReceive('make')->andReturnSelf();
        $view->shouldReceive('render')->andReturn('foo');

        $this->expectException(\Illuminate\Contracts\Filesystem\FileNotFoundException::class);

        (new Recaptcha($config, $request, $view, $guzzle))->render();
    }

    /**
     * Verify
     */
    public function testVerifyReturnsTrue()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $success = new \stdClass();
        $success->success = true;

        $request->shouldReceive('has')->withAnyArgs()->andReturn(true);
        $request->shouldReceive('get')->withAnyArgs()->andReturn('foo');
        $request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $guzzle->shouldReceive('request')->withAnyArgs()->andReturnSelf();
        $guzzle->shouldReceive('getBody')->andReturn(json_encode($success));

        $this->assertTrue((new Recaptcha($config, $request, $view, $guzzle))->verify());
    }

    /**
     * Verify when missing request field
     */
    public function testVerifyReturnsFalseWhenMissingRequest()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $request->shouldReceive('has')->withAnyArgs()->andReturn(false);

        $this->assertFalse((new Recaptcha($config, $request, $view, $guzzle))->verify());
    }

    /**
     * Test returns false when recaptcha failed validation
     */
    public function testVerifyReturnsFalseWhenReturnedDataIsWrong()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $success = new \stdClass();
        $success->success = false;

        $request->shouldReceive('has')->withAnyArgs()->andReturn(true);
        $request->shouldReceive('get')->withAnyArgs()->andReturn('foo');
        $request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $guzzle->shouldReceive('request')->withAnyArgs()->andReturnSelf();
        $guzzle->shouldReceive('getBody')->andReturn(json_encode($success));

        $this->assertFalse((new Recaptcha($config, $request, $view, $guzzle))->verify());
    }

        /**
     * Test redirect
     */
    public function testRedirect()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $this->get('/foo', ['referer' => 'https://foo.com/recaptcha']);

        $redirect = (new Recaptcha($config, $request, $view, $guzzle))->redirect();

        $this->assertSame(302, $redirect->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertSame('https://foo.com/recaptcha', $redirect->getTargetUrl());
    }

    /**
     * @param bool   $enabled
     * @param string $env
     * @return array
     */
    protected function getConstructorArgs(bool $enabled = true, string $env = 'testing', bool $guests_only = false)
    {
        $config = new Repository([
            'app' => [
                'env' => $env,
            ],
            'antispam' => [
                'recaptcha' => [
                    'enabled'   => $enabled,
                    'view'      => 'layout.antispam.recaptcha',
                    'key'       => 'key',
                    'secret'    => 'secret',

                    // Disabled envs
                    'disabled_env' => [
                        'disabled1',
                        'disabled2',
                    ],

                    // Name of js file that contains the callback function
                    'js_callback' => 'fooscript',

                    // If true, will force all users to complete
                    // Otherwise, will allow logged in users to skip
                    'guests_only' => $guests_only,
                ],
            ],
        ]);

        return [
            $config,
            m::mock(Request::class),
            m::mock(ViewFactory::class),
            m::mock(GuzzleClient::class),
        ];
    }
}
