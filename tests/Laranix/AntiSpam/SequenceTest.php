<?php
namespace Laranix\Tests\Laranix\AntiSpam;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\RedirectResponse;
use Laranix\AntiSpam\Sequence\Sequence;
use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Tests\LaranixTestCase;

class SequenceTest extends LaranixTestCase
{
    /**
     * Test when enabled is false
     */
    public function testWhenEnabledIsFalse()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs(false);

        $sequence = new Sequence($config, $request, $view);

        $this->assertFalse($sequence->enabled());
        $this->assertNull($sequence->render());
        $this->assertTrue($sequence->verify());
        $this->assertNull($sequence->redirect());
    }

    /**
     * Test when recaptcha disabled due to environment
     */
    public function testWhenEnvironmentIsDisabled()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs(true, 'disabled1');

        $sequence = new Sequence($config, $request, $view);

        $this->assertFalse($sequence->enabled());
        $this->assertNull($sequence->render());
        $this->assertTrue($sequence->verify());
        $this->assertNull($sequence->redirect());
    }

    /**
     * Test render
     */
    public function testRenderWhenEnabled()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $view->shouldReceive('exists')->andReturn(true);
        $view->shouldReceive('make')->andReturnSelf();
        $view->shouldReceive('render')->andReturn('output');

        $session = m::mock(Session::class);
        $session->shouldReceive('put')->withAnyArgs()->andReturnNull();

        $request->shouldReceive('session')->andReturn($session);

        $this->assertSame('output', (new Sequence($config, $request, $view))->render());
    }

    /**
     * Test when view not found
     */
    public function testRenderThrowsExceptionWhenViewNotFound()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $view->shouldReceive('exists')->withAnyArgs()->andReturn(false);
        $view->shouldReceive('make')->withAnyArgs()->andReturnSelf();
        $view->shouldReceive('render')->withNoArgs()->andReturn('foo');

        $recaptcha = new Sequence($config, $request, $view);

        $this->expectException(\Illuminate\Contracts\Filesystem\FileNotFoundException::class);
        $recaptcha->render();
    }

    /**
     * Test when field not set
     */
    public function testVerifyReturnsFalseWhenFieldNotFound()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $request->shouldReceive('has')->andReturn(false);

        $this->assertFalse((new Sequence($config, $request, $view))->verify());
    }

    /**
     * Test when field not set
     */
    public function testVerifyReturnsFalseWhenSessionNotSet()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $request->shouldReceive('has')->andReturn(true);

        $session = m::mock(Session::class);
        $session->shouldReceive('get')->withAnyArgs()->andReturnNull();

        $request->shouldReceive('session')->withNoArgs()->andReturn($session);

        $this->assertFalse((new Sequence($config, $request, $view))->verify());
    }

    /**
     * Test verify returns true
     */
    public function testVerifyReturnsFalseWhenSequenceDoesNotAddUp()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $value = random_int(100, 10000);
        $add = random_int(10, 1000);

        $data = [
            'value'     => $value,
            'add'       => $add,
            'total'     => $value,
        ];

        $session = m::mock(Session::class);
        $session->shouldReceive('get')
                ->with('__form_sequence_value')
                ->andReturn(['total' => $data['total'], 'add' => $data['add'], 'value' => $data['value']]);

        $session->shouldReceive('get')
                ->with('__form_sequence_value.total', -1)
                ->andReturn($data['total'] + ($data['add'] + 1));

        $session->shouldReceive('increment')->withAnyArgs()->andReturnNull();
        $session->shouldReceive('keep')->withAnyArgs()->andReturnNull();

        $request->shouldReceive('has')->withAnyArgs()->andReturn(true);
        $request->shouldReceive('session')->withNoArgs()->andReturn($session);
        $request->shouldReceive('get')->withAnyArgs()->andReturn($data['value']);

        $this->assertFalse((new Sequence($config, $request, $view))->verify());
    }

    /**
     * Test verify returns true
     */
    public function testVerifyReturnsTrue()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $value = random_int(100, 10000);
        $add = random_int(10, 1000);

        $data = [
            'value'     => $value,
            'add'       => $add,
            'total'     => $value,
        ];

        $session = m::mock(Session::class);
        $session->shouldReceive('get')
                ->with('__form_sequence_value')
                ->andReturn(['total' => $data['total'], 'add' => $data['add'], 'value' => $data['value']]);

        $session->shouldReceive('get')
                ->with('__form_sequence_value.total', -1)
                ->andReturn($data['total'] + $data['add']);

        $session->shouldReceive('increment')->withAnyArgs()->andReturnNull();
        $session->shouldReceive('forget')->withAnyArgs()->andReturnNull();

        $request->shouldReceive('has')->withAnyArgs()->andReturn(true);
        $request->shouldReceive('session')->withNoArgs()->andReturn($session);
        $request->shouldReceive('get')->withAnyArgs()->andReturn($data['value']);

        $this->assertTrue((new Sequence($config, $request, $view))->verify());
    }

    /**
     * Test redirect
     */
    public function testRedirect()
    {
        list($config, $request, $view, $guzzle) = $this->getConstructorArgs();

        $this->get('/foo', ['referer' => 'https://foo.com/recaptcha']);

        $redirect = (new Sequence($config, $request, $view))->redirect();

        $this->assertSame(302, $redirect->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertSame('https://foo.com/recaptcha', $redirect->getTargetUrl());
    }

    /**
     * @param bool   $enabled
     * @param string $env
     * @return array
     */
    protected function getConstructorArgs(bool $enabled = true, string $env = 'testing')
    {
        $config = new Repository([
            'app' => [
                'env' => $env,
            ],
            'antispam' => [
                'sequence' => [
                    'enabled'       => $enabled,
                    'view'          => 'layout.antispam.sequence',
                    'field_name'    => '__sequence_id',

                    // Disabled envs
                    'disabled_env' => [
                        'disabled1',
                        'disabled2',
                    ],
                ]
            ]
        ]);

        return [
            $config,
            m::mock(Request::class),
            m::mock(ViewFactory::class),
            m::mock(GuzzleClient::class),
        ];
    }
}
