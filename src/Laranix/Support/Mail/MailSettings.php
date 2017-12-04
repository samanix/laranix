<?php
namespace Laranix\Support\Mail;

use Laranix\Support\Settings;

class MailSettings extends Settings
{
    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'to'            => 'email|array',
        'view'          => 'string',
        'subject'       => 'string',
        'attachments'   => 'optional|array',
        'rawAttachments'=> 'optional|array',
    ];

    /*
     |--------------------------------------------------------------------------
     | Mail Addresses
     |--------------------------------------------------------------------------
     |
     | Addresses for the mail
     |
     */
    /**
     * To addresses
     *
     * @var string|array
     */
    public $to;

    /**
     * From address
     *
     * @var object|array|string
     */
    public $from;

    /**
     * CC addresses
     *
     * @var object|array|string
     */
    public $cc;

    /**
     * BCC addresses
     *
     * @var object|array|string
     */
    public $bcc;

    /**
     * Reply to addresses
     *
     * @var object|array|string
     */
    public $replyTo;

    /*
     |--------------------------------------------------------------------------
     | Mail Settings
     |--------------------------------------------------------------------------
     |
     | Mail settings
     |
     */
    /**
     * @var bool
     */
    public $markdown = true;

    /**
     * @var string
     */
    public $view;

    /**
     * @var array
     */
    public $extraViewData;

    /**
     * @var string
     */
    public $textView;

    /**
     * @var array
     */
    public $textExtraViewData;

    /**
     * @var string
     */
    public $subject;

    /**
     * Attachments
     *
     * @var array|null
     */
    public $attachments;

    /**
     * Raw attachments
     *
     * @var array|null
     */
    public $rawAttachments;

    /*
     |--------------------------------------------------------------------------
     | Mail Variables
     |--------------------------------------------------------------------------
     |
     | Variables for use in the mail template
     |
     */
    /**
     * @var string
     */
    public $message;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $fullName;
}
