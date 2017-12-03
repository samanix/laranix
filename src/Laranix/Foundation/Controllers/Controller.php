<?php
namespace Laranix\Foundation\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Laranix\AntiSpam\Sequence\Sequence;
use Laranix\AntiSpam\Recaptcha\Recaptcha;
use Laranix\Support\IO\LoadsViews;
use Laranix\Support\IO\Url\Url;
use Laranix\Themer\LoadsThemer;
use Laranix\Themer\ResourceSettings as ThemerFileSettings;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, LoadsThemer, LoadsViews;

    /**
     * @var bool
     */
    protected $preparedForResponse = false;

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\Support\IO\Url\Url
     */
    protected $url;

    /**
     * Ignore paths for themer auto init
     *
     * @var array
     */
    protected $autoPrepareResponseExcept = [];

    /**
     * LaranixBaseController constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $application
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    public function __construct(Application $application)
    {
        $this->app      = $application;
        $this->request  = $this->app->make('request');
        $this->config   = $this->app->make('config');
        $this->url      = $this->app->make(Url::class);

        if ($this->shouldAutoPrepareForResponse()) {
            $this->prepareForResponse();
        }
    }

    /**
     * POST data
     *
     * @param string|null   $key
     * @param mixed         $default
     * @return mixed
     */
    protected function getPostData(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->request->request;
        }

        return $this->request->request->get($key, $default);
    }

    /**
     * GET data
     *
     * @param string|null   $key
     * @param mixed         $default
     * @return mixed
     */
    protected function getQueryData(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->request->query;
        }

        return $this->request->query->get($key, $default);
    }

    /**
     * Get the session
     *
     * @param string|null   $key
     * @param mixed         $default
     * @return mixed
     */
    protected function getSessionData(string $key = null, $default = null)
    {
        $session = $this->request->session();

        if ($key === null) {
            return $session;
        }

        return $session->get($key, $default);
    }

    /**
     * Determine if we automatically prepare for a response from a request
     *
     * @return bool
     */
    protected function shouldAutoPrepareForResponse(): bool
    {
        if ($this->request->isMethod('get')
            && !in_array($this->request->path(), $this->autoPrepareResponseExcept)) {
            return true;
        }

        return false;
    }

    /**
     * Prepare for a request response
     *
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function prepareForResponse()
    {
        if ($this->preparedForResponse) {
            return;
        }

        $this->loadView($this->app);

        $this->loadThemer($this->app);
        $this->loadThemerDefaultFiles($this->config);

        $this->loadGlobalViewVariables($this->app, $this->config);

        $this->preparedForResponse = true;
    }

    /**
     * Add parts required for rendering a form
     *
     * @param bool                          $withRecaptcha
     * @param array|ThemerFileSettings|null $scripts
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     */
    protected function prepareForFormResponse(bool $withRecaptcha = true, ...$scripts)
    {
        if ($withRecaptcha) {
            $recaptcha = $this->app->make(Recaptcha::class);
        }

        $this->share([
            'sequence'  => $this->app->make(Sequence::class),
            'recaptcha' => $recaptcha ?? null,
        ]);

        $this->loadThemerDefaultFormFiles($this->config, $recaptcha ?? null, $scripts);
    }

    /**
     * Validate a request
     *
     * @param array      $rules
     * @param array|null $data
     */
    protected function validate(array $rules, array $data = null)
    {
        $data = $data ?? $this->getPostData()->all();

        $this->app->make('validator')->make($data, $rules)->validate();
    }
}
