<?php
namespace Laranix\Support\IO\Str;

class Str
{
    /**
     * Parse string to formatted output.
     *
     * @param string                                 $string
     * @param array                                  $values
     * @param \Laranix\Support\IO\Str\Settings|array $options
     *
     * @return string
     */
    public static function format(string $string, array $values, $options = null) : string
    {
        if (is_array($options)) {
            $options = new Settings($options);
        } elseif ($options === null) {
            $options = new Settings();
        }

        $options->hasRequiredSettings();

        return self::parseString($string, $values, $options);
    }

    /**
     * Format the string.
     *
     * @param string                           $string
     * @param array                            $values
     * @param \Laranix\Support\IO\Str\Settings $options
     *
     * @return string
     */
    protected static function parseString(string $string, array $values, Settings $options) : string
    {
        $output = str_replace(array_map(function ($value) use ($options) {
            return $options->leftSeparator . $value . $options->rightSeparator;
        }, array_keys($values)), array_values($values), $string);

        if ($options->removeUnparsed
            && strpos($output, $options->leftSeparator) !== false
            && strpos($output, $options->rightSeparator) !== false) {
            $output = preg_replace(
                sprintf('/%s([a-zA-Z0-9_\\.\\-]+)%s/', $options->leftSeparator, $options->rightSeparator),
                $options->unparsedReplacement,
                $output
            );
        }

        if ($options->removeExtraSpaces) {
            $output = preg_replace('/\s+/', ' ', $output);
        }

        return trim($output);
    }
}
