<?php
namespace Laranix\Recaptcha;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory as ViewFactory;

class Recaptcha
{
    const SESSION_NAME = '__recaptcha_active';

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * Recaptcha constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Http\Request                $request
     * @param \Illuminate\Contracts\View\Factory      $view
     */
    public function __construct(Repository $config, Request $request, ViewFactory $view)
    {
        $this->config = $config;
        $this->request = $request;
        $this->view = $view;
    }

    /**
     * Render view.
     *
     * @param string|null $view
     * @param array       $data
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function render(?string $view = null, array $data = []): ?string
    {
        if (!$this->enabled()) {
            return null;
        }

        $useView = $view !== null ?
            $view : $this->config->get('recaptcha.view', 'layout.recaptcha');

        if ($this->view->exists($useView)) {
            return $this->view->make($useView, $this->getViewData($data))->render();
        }

        throw new FileNotFoundException("View '{$useView}' not found");
    }

    /**
     * Check if Recaptcha enabled
     *
     * @return bool
     */
    public function enabled(): bool
    {
        if ($this->isEnabled !== null) {
            return $this->isEnabled;
        }

        if (!$this->config->get('recaptcha.enabled', true)) {
            return $this->isEnabled = false;
        }

        $disabledIn = (array) $this->config->get('recaptcha.disabled_in', []);

        if (in_array($this->config->get('app.env', 'production'), $disabledIn)) {
            return $this->isEnabled = false;
        }

        if ($this->config->get('recaptcha.guests_only', false) &&
            $this->request->user() !== null) {
            return $this->isEnabled = false;
        }

        return $this->isEnabled = true;
    }

    /**
     * Get view data
     *
     * @param array|null $data
     * @return array
     */
    protected function getViewData(?array $data = []): array
    {
        return [
            'recaptcha' => array_merge(
                ['key' => $this->config->get('recaptcha.key')],
                $data
            )
        ];
    }

    /**
     * Assign in status that recaptcha is active
     */
    public function activate()
    {
        $this->request->session()
                      ->put(self::SESSION_NAME, true);
    }

    /**
     * Deactivate recaptcha being active
     */
    public function deactivate()
    {
        $this->request->session()
                      ->put(self::SESSION_NAME, true);
    }

    /**
     * Check if recaptcha active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->request->session()
                             ->get(self::SESSION_NAME, false) === true;
    }
}
