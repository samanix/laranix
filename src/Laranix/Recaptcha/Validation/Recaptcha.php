<?php
namespace Laranix\Recaptcha\Validation;

use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\Rule;
use Laranix\Recaptcha\Recaptcha as RecaptchaService;

class Recaptcha implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function passes($attribute, $value)
    {
        // If its not enabled then just validate
        if (!$this->enabled()) {
            return true;
        }

        $result = $this->getRecaptchaResult($value);

        return isset($result->success) ? (bool) $result->success : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Recaptcha is not valid.';
    }

    /**
     * Perform curl request
     *
     * @param $value
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getRecaptchaResult($value)
    {
        $response = (new Client())->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'timeout'   => 2.0,
                'query'     => [
                    'secret'    => config('recaptcha.secret', ''),
                    'response'  => $value,
                    'remoteip'  => request()->getClientIp(),
                ],
            ]
        );

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Check if enabled
     *
     * @return bool
     */
    protected function enabled(): bool
    {
        /** @var \Laranix\Recaptcha\Recaptcha $recaptcha */
        $recaptcha = app(RecaptchaService::class);

        return $recaptcha->enabled();
    }
}
