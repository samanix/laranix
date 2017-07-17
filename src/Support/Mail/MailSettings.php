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
        'attachments'   => 'null|array'
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


    /*
     |--------------------------------------------------------------------------
     | Mail Variables
     |--------------------------------------------------------------------------
     |
     | Variables for use in the mail template
     |
     */
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
    public $first_name;

    /**
     * @var string
     */
    public $last_name;

    /**
     * @var string
     */
    public $full_name;

}
