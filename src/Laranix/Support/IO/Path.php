<?php
namespace Laranix\Support\IO;

class Path
{
    /**
     * Combine files/folders in to path.
     *
     * @param \null[]|\string[] ...$parts
     * @return null|string
     */
    public static function combine(?string ...$parts) : ?string
    {
        if (empty($parts)) {
            return null;
        }

        $maxIndex = count($parts) - 1;
        $path = [];

        foreach ($parts as $index => $part) {
            if ($part !== '0' && empty($part)) {
                continue;
            }

            $part = str_replace('\\', '/', $part);

            if ($index === 0) {
                $path[] = rtrim($part, '/');
            } elseif ($index === $maxIndex) {
                $path[] = ltrim($part, '/');
            } else {
                $path[] = trim($part, '/');
            }
        }

        if (empty($path)) {
            return null;
        }

        $fullPath = implode('/', $path);

        if (is_dir($fullPath) || is_file($fullPath)) {
            return realpath($fullPath);
        }

        return $fullPath;
    }
}
