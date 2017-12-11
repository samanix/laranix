<?php
namespace Laranix\AntiSpam\Recaptcha;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Laranix\AntiSpam\AntiSpam;

class Recaptcha extends AntiSpam
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * Recaptcha constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Http\Request                $request
     * @param \Illuminate\Contracts\View\Factory      $viewFactory
     * @param \GuzzleHttp\Client                      $guzzle
     */
    public function __construct(Config $config, Request $request, ViewFactory $viewFactory, GuzzleClient $guzzle)
    {
        parent::__construct($config, $request, $viewFactory);

        $this->guzzle = $guzzle;
    }

    /**
     * Get view data.
     *
     * @param string $formId
     * @return array
     */
    protected function getViewData(?string $formId = null) : array
    {
        return [
            'recaptcha' => [
                'siteKey'   => $this->config->get('antispam.recaptcha.key', ''),
                'formId'    => $formId,
            ],
        ];
    }

    /**
     * Verify form request.
     *
     * @return bool
     */
    protected function verifyRequest() : bool
    {
        if (!$this->request->has('g-recaptcha-response')) {
            $this->redirectMessage = 'Recaptcha was not completed';

            return false;
        }

        $data = $this->getRecaptchaResult();

        $success = isset($data->success) ? (bool) $data->success : false;

        if ($success) {
            return true;
        }

        $this->redirectMessage = 'Recaptcha was invalid';

        return false;
    }

    /**
     * Perform curl request
     *
     * @return mixed
     */
    protected function getRecaptchaResult()
    {
        $response = $this->guzzle->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'secret'    => $this->config->get('antispam.recaptcha.secret', ''),
            'response'  => $this->request->get('g-recaptcha-response', ''),
            'remoteip'  => $this->request->getClientIp(),
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
