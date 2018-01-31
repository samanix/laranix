<?php
namespace Laranix\AntiSpam;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laranix\AntiSpam\Recaptcha\Recaptcha;

abstract class AntiSpam
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Store class type.
     *
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $instanceType;

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * Message to provide with redirect.
     *
     * @var string
     */
    protected $redirectMessage;

    /**
     * Get view data.
     *
     * @param string $formId
     * @return array
     */
    abstract protected function getViewData(?string $formId = null) : array;

    /**
     * Verify form request.
     *
     * @return bool
     */
    abstract protected function verifyRequest() : bool;

    /**
     * FormBase constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository      $config
     * @param \Illuminate\Http\Request           $request
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     */
    public function __construct(Config $config, Request $request, ViewFactory $viewFactory)
    {
        $this->instanceType = $this->getInstanceType();
        $this->config       = $config;
        $this->request      = $request;
        $this->viewFactory  = $viewFactory;
        $this->view         = $this->config->get("antispam.{$this->instanceType}.view", "layout.antispam.{$this->instanceType}");
        $this->enabled      = $this->enabled();
    }

    /**
     * Get instance type.
     *
     * @return  string
     */
    protected function getInstanceType() : string
    {
        return get_class($this) === Recaptcha::class ? 'recaptcha' : 'sequence';
    }

    /**
     * Render view.
     *
     * @param string|null   $formId
     * @param string|null   $view
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function render(?string $formId = null, ?string $view = null) : ?string
    {
        if (!$this->enabled()) {
            return null;
        }

        $useView = $view !== null ? $view : $this->view;

        if ($this->viewFactory->exists($useView)) {
            return $this->viewFactory->make($useView, $this->getViewData($formId))->render();
        }

        throw new FileNotFoundException("View '{$useView}' not found");
    }

    /**
     * Verify form data coming in from request.
     *
     * @return bool
     */
    public function verify() : bool
    {
        // If not enabled, return that its valid
        if (!$this->enabled()) {
            return true;
        }

        return $this->verifyRequest();
    }

    /**
     * Check enabled status.
     *
     * @return bool
     */
    public function enabled() : bool
    {
        if ($this->isEnabled !== null) {
            return $this->isEnabled;
        }

        if (!$this->config->get("antispam.{$this->instanceType}.enabled", true)) {
            return $this->isEnabled = false;
        }

        $disabledEnv = (array) $this->config->get("antispam.{$this->instanceType}.disabled_env", []);

        if (in_array($this->config->get('app.env', 'production'), $disabledEnv)) {
            return $this->isEnabled = false;
        }

        if ($this->instanceType === 'recaptcha') {
            if ($this->config->get('antispam.recaptcha.guests_only', false) && $this->request->user() !== null) {
                return $this->isEnabled = false;
            }
        }

        return $this->isEnabled = true;
    }

    /**
     * Redirect on failed form submission
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect() : ?RedirectResponse
    {
        if (!$this->enabled()) {
            return null;
        }

        return redirect()
            ->back()
            ->withErrors([
                'form-verify-error' => $this->redirectMessage,
            ])
            ->withInput();
    }
}
