<?php
namespace Laranix\Support\IO\Url;

class Href extends UrlCreator
{
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
        $cacheKey = $this->getCacheKey($content, $url, $attributes);

        if ($this->hasCachedData($cacheKey)) {
            return $this->getCachedData($cacheKey);
        }

        return $this->cacheData($cacheKey,
                                $this->createHrefHtmlOutput($content, $url, $attributes));
    }

    /**
     * HTML href output alias
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
     *
     *
     * @param string $content
     * @param string $url
     * @param array  $attributes
     * @return string
     */
    protected function createHrefHtmlOutput(string $content, string $url, array $attributes = []) : string
    {
        return sprintf('<a href="%s"%s>%s</a>',
                       $url,
                       $this->parseHrefAttributes($attributes),
                       $content);
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

        $extra = [];

        foreach ($attributes as $attr => $value) {
            $extra[] = $attr . '="' . $value . '"';
        }

        return ' ' . implode(' ', $extra);
    }
}
