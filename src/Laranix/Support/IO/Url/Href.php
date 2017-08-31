<?php
namespace Laranix\Support\IO\Url;

use Laranix\Support\Settings;
use Illuminate\Support\Str;

class Href extends UrlCreator
{
    /**
     * @var \Laranix\Support\IO\Url\Url
     */
    protected $url;

    /**
     * Href constructor.
     *
     * @param string                      $appUrl
     * @param \Laranix\Support\IO\Url\Url $url
     */
    public function __construct(string $appUrl, Url $url)
    {
        parent::__construct($appUrl);

        $this->url = $url;
    }

    /**
     * HTML href output
     *
     * @param string $content
     * @param string $url
     * @param array  $attributes
     * @return string
     */
    public function create(string $content, string $url, array $attributes = []) : string
    {
        return $this->make(new HrefSettings([
            'content'   => $content,
            'url'       => $url,
            'attributes'=> $attributes,
        ]));
    }

    /**
     * Create alias
     *
     * @param string $content
     * @param string $url
     * @param array  $attributes
     * @return string
     */
    public function to(string $content, string $url, array $attributes = []) : string
    {
        return $this->create($content, $url, $attributes);
    }

    /**
     * Create and return string output
     *
     * @param \Laranix\Support\Settings|\Laranix\Support\IO\Url\HrefSettings $settings
     * @return string
     */
    protected function createOutput(Settings $settings) : string
    {
        return sprintf('<a href="%s"%s>%s</a>',
                       $this->parseUrl($settings->url),
                       $this->parseHrefAttributes($settings->attributes),
                       $settings->content);
    }

    /**
     * Parse Url
     *
     * @param mixed $url
     * @return string
     */
    protected function parseUrl($url) : string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) !== false
            || Str::startsWith($url, '#')) {
            return $url;
        }


        return $this->url->url($url);
    }

    /**
     * Create attributes string for href
     *
     * @param array|null $attributes
     * @return null|string
     */
    protected function parseHrefAttributes(?array $attributes) : ?string
    {
        if (empty($attributes)) {
            return null;
        }

        if (isset($attributes['target']) && $attributes['target'] === '_blank') {
            if (!isset($attributes['rel'])) {
                $attributes['rel'] = 'noreferrer noopener';
            }
        }

        $extra = [];

        foreach ($attributes as $attr => $value) {
            $extra[] = $attr . '="' . $value . '"';
        }

        return ' ' . implode(' ', $extra);
    }
}
