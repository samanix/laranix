<?php
namespace Laranix\Support\Mail;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Support\Facades\Log;
use Laranix\Support\PropertyValidator;
use Laranix\Support\ValidatesRequiredProperties;

abstract class Mailer implements PropertyValidator
{
    use ValidatesRequiredProperties;

    /**
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var string
     */
    protected $mailable;

    /**
     * @var string
     */
    protected $settings = MailSettings::class;

    /**
     * Mailer constructor.
     *
     * @param \Illuminate\Contracts\Mail\Mailer       $mailer
     * @param \Illuminate\Contracts\Config\Repository $config
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    public function __construct(MailerContract $mailer, Repository $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;

        $this->validateProperties([
            'mailable'  => 'is_a:' . Mail::class,
            'settings'  => 'is_a:' . MailSettings::class,
        ]);
    }

    /**
     * Create and send mail
     *
     * @param mixed $data
     * @return \Laranix\Support\Mail\MailSettings
     * @throws \Exception
     */
    public function send($data = null): ?MailSettings
    {
        try {
            $settings = $this->createSettings($data);

            $settings->hasRequiredSettings();

            $this->mailer->send(
                $this->createMailable($settings)
            );
        } catch (\Exception $e) {
            Log::error($e);

            $env = $this->config->get('app.env', 'production');

            if (!in_array($env, ['production', 'prod', 'live'])) {
                throw $e;
            }
        }

        return $settings ?? null;
    }

    /**
     * Get the mailable
     *
     * @param \Laranix\Support\Mail\MailSettings $settings
     * @return mixed
     */
    public function createMailable(MailSettings $settings): Mail
    {
        return new $this->mailable($settings);
    }

    /**
     * Create mail settings
     *
     * @param mixed $data
     * @return \Laranix\Support\Mail\MailSettings
     */
    protected function createSettings($data = null): MailSettings
    {
        if ($data === null && method_exists($this, 'getPayload')) {
            $data = $this->getPayload();
        }

        if ($data instanceof MailSettings) {
            return $data;
        }

        return new $this->settings($data);
    }
}
