<?php
namespace Laranix\Support\IO\Url;

use Laranix\Support\Settings;

class UrlSettings extends Settings
{
    /**
     * Scheme to use.
     *
     * @var string
     */
    public $scheme;

    /**
     * Domain.
     *
     * @var string
     */
    public $domain;

    /**
     * Path options to append to domain.
     *
     * @var string|array
     */
    public $path;

    /**
     * Query string.
     *
     * @var array
     */
    public $query;

    /**
     * Fragment
     *
     * @var string
     */
    public $fragment;

    /**
     * If true, will add trailing slash.
     *
     * @var bool
     */
    public $trailingSlash = false;
}
