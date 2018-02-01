<?php
namespace Laranix\Auth\Password\Reset;

use Laranix\Auth\User\Token\MailSettings;
use Laranix\Support\Mail\Mailer as MailerBase;

class Mailer extends MailerBase
{
    /**
     * @inheritDoc
     */
    protected $mailable = Mail::class;

    /**
     * @inheritDoc
     */
    protected $settings = MailSettings::class;
}
