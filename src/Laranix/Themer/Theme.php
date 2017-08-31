<?php
namespace Laranix\Themer;

use Illuminate\Support\Str;

class Theme
{
    /**
     * @var \Laranix\Themer\ThemeSettings
     */
    protected $settings;

    /**
     * @var string
     */
    protected $fullWebPath;

    /**
     * @var string
     */
    protected $fullPath;

    /**
     * @var bool
     */
    protected $verified;

    /**
     * Theme constructor.
     *
     * @param \Laranix\Themer\ThemeSettings $settings
     */
    public function __construct(ThemeSettings $settings)
    {
        $this->settings = $settings;

        $settings->hasRequiredSettings();
    }

    /**
     * Get setting
     *
     * @param string      $key
     * @return mixed
     */
    public function getSetting(string $key)
    {
        if (isset($this->settings->{$key})) {
            return $this->settings->{$key};
        }

        return null;
    }

    /**
     * Get theme key
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->getSetting('key');
    }

    /**
     * Get theme name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->getSetting('name');
    }

    /**
     * Get path to theme
     *
     * @return string
     */
    public function getPath() : string
    {
        if ($this->fullPath !== null) {
            return $this->fullPath;
        }

        $path = $this->getSetting('path');

        if (is_dir($path)) {
            $this->verified = true;

            return $this->fullPath = realpath($path);
        }

        $pubPath = public_path(trim($path, '/'));

        $this->verified = is_dir($pubPath);

        return $this->fullPath = $pubPath;
    }

    /**
     * Get web path for theme
     *
     * @return string
     */
    public function getWebPath() : string
    {
        if ($this->fullWebPath !== null) {
            return $this->fullWebPath;
        }

        $path = rtrim($this->getSetting('webPath'), '/');

        if (filter_var($path, FILTER_VALIDATE_URL) !== false) {
            return $this->fullWebPath = $path;
        }

        if (Str::startsWith($path, '//')) {
            return $this->fullWebPath = $path;
        }

        return $this->fullWebPath = urlTo(ltrim($path, '/'));
    }

    /**
     * Check if theme is verified, i.e. Directory exists in given path
     *
     * @return bool
     */
    public function verified() : bool
    {
        if ($this->verified !== null) {
            return $this->verified;
        }

        return $this->verified = is_dir($this->getPath());
    }
}
