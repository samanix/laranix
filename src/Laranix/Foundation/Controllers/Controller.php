<?php
namespace Laranix\Foundation\Controllers;

use Illuminate\Contracts\Config\Repository;
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
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Laranix\Support\IO\Url\Url             $url
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    public function __construct(Repository $config, Url $url)
    {
        $this->config   = $config;
        $this->url      = $url;

        if ($this->shouldAutoPrepareForResponse()) {
            $this->prepareForResponse();
        }
    }

    /**
     * Determine if we automatically prepare for a response from a request
     *
     * @return bool
     */
    protected function shouldAutoPrepareForResponse(): bool
    {
        $request = request();

        return $request->isMethod('get') &&
            !in_array($request->path() ?? '', $this->autoPrepareResponseExcept);
    }

    /**
     * Prepare for a request response
     *
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    protected function prepareForResponse()
    {
        if ($this->preparedForResponse) {
            return;
        }

        $this->loadView();
        $this->loadThemer();
        $this->loadThemerDefaultFiles($this->config);
        $this->loadGlobalViewVariables($this->config);

        if (method_exists($this, 'prepareExtraForResponse')) {
            $this->prepareExtraForResponse();
        }

        $this->preparedForResponse = true;
    }

    /**
     * Add parts required for rendering a form
     *
     * @param bool                          $withRecaptcha
     * @param array|ThemerFileSettings|null $scripts
     * @throws \Laranix\Support\Exception\InvalidInstanceException
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    protected function prepareForFormResponse(bool $withRecaptcha = true, ...$scripts)
    {
        if ($withRecaptcha) {
            $recaptcha = app()->make(Recaptcha::class);
        }

        $this->share([
            'sequence'  => app()->make(Sequence::class),
            'recaptcha' => $recaptcha ?? null,
        ]);

        $this->loadThemerDefaultFormFiles($this->config, $recaptcha ?? null, ...$scripts);
    }
}
