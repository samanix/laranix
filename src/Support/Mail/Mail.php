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
            $options->hasRequiredSettings();
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
            $this->markdown($this->options->view, $this->options->extraViewData ?? []);
        } else {
            $this->view($this->options->view, $this->options->extraViewData ?? []);
        }

        if ($this->options->textView !== null) {
            $this->text($this->options->textView, $this->options->textExtraViewData ?? []);
        }

        if ($this->options->attachments !== null) {
            if (isset($this->options->attachments['file'])) {
                $this->attach($this->options->attachments['file'], $this->options->attachments['options'] ?? []);
            } else {
                foreach ($this->options->attachments as $id => $attachment) {
                    $this->attach($attachment['file'], $attachment['options'] ?? []);
                }
            }
        }

        if ($this->options->rawAttachments !== null) {
            if (isset($this->options->rawAttachments['data'])) {
                $this->attachData(
                    $this->options->rawAttachments['data'],
                    $this->options->rawAttachments['name'],
                    $this->options->rawAttachments['options'] ?? []
                );
            } else {
                foreach ($this->options->rawAttachments as $id => $attachment) {
                    $this->attachData($attachment['data'], $attachment['name'], $attachment['options'] ?? []);
                }
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
