<?php
namespace Laranix\Support\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Laranix\Support\Exception\NullValueException;

class Mail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Mail options
     *
     * Must be public to allow view to access
     *
     * @var \Laranix\Support\Mail\MailSettings
     */
    public $options;

    /**
     * EmailVerificationMail constructor.
     *
     * @param \Laranix\Support\Mail\MailSettings|null $options
     */
    public function __construct(?MailSettings $options = null)
    {
        if ($options !== null) {
            $options->hasRequired();
            $this->options = $options;
            $this->setupMail();
        }
    }

    /**
     * Set options
     *
     * @param \Laranix\Support\Mail\MailSettings $options
     * @return $this
     */
    public function setOptions(MailSettings $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Setup mail values
     */
    public function setupMail()
    {
        if ($this->options === null) {
            throw new NullValueException('Mail options not set on ' . get_class($this));
        }

        $params = ['to', 'from', 'cc', 'bcc', 'replyTo', 'subject'];

        foreach ($params as $param) {
            if (isset($this->options->$param)) {
                $this->$param($this->options->$param);
            }
        }

        if ($this->options->markdown === true) {
            $this->markdown($this->options->view);
        }

        if (isset($this->options->attachments)) {
            if (is_array($this->options->attachments)) {
                foreach ($this->options->attachments as $attachment) {
                    $this->attach($attachment['file'], $attachment['options']);
                }
            } else {
                $this->attach($this->options->attachments);
            }
        }
    }

    /**
     * Build the message
     *
     * @return $this
     */
    public function build()
    {
        return $this;
    }
}
