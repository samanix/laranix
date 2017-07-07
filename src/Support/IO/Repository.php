<?php
namespace Laranix\Support\IO;

use ArrayAccess;

class Repository implements ArrayAccess
{
    /**
     * Array values.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Array key separator.
     *
     * @var string
     */
    protected $keySeparator = '.';

    /**
     * LaranixArrayAccess constructor.
     *
     * @param array  $values
     * @param string $separator
     */
    public function __construct(array $values = [], string $separator = '.')
    {
        if (!empty($values)) {
            $this->set($values);
        }

        $this->keySeparator = $separator;
    }

    /**
     * Add values.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function add($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Set values.
     *
     * @param array|string $value
     * @param string       $option
     * @param bool         $replace
     *
     * @return $this
     */
    public function set($value, $option = null, bool $replace = true)
    {
        if (is_array($value)) {
            if (empty($this->values)) {
                $this->values = $value;
            } else {
                $replace ? $this->replace($value) : $this->merge($value);
            }
        } else {
            $this->add($value, $option);
        }

        return $this;
    }

    /**
     * Merge values in.
     *
     * @param array $values
     *
     * @return $this
     */
    public function merge(array $values)
    {
        $this->values = array_merge_recursive($this->values, $values);

        return $this;
    }

    /**
     * Replace values in repository
     *
     * @param array $values
     * @return $this
     */
    public function replace(array $values)
    {
        $this->values = array_replace_recursive($this->values, $values);

        return $this;
    }

    /**
     * Set key separator.
     *
     * @param string $separator
     *
     * @return $this|string
     */
    public function setSeparator(string $separator)
    {
        $this->keySeparator = $separator;

        return $this;
    }

    /**
     * Set key separator.
     *
     * @return string
     */
    public function getSeparator() : string
    {
        return $this->keySeparator;
    }

    /**
     * Get key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return $default;
    }

    /**
     * Get all values.
     *
     * @return array
     */
    public function all() : array
    {
        return $this->values;
    }

    /**
     * Check for existence.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key = null) : bool
    {
        if ($key === null) {
            return false;
        }

        return $this->offsetExists($key);
    }

    /**
     * Whether a offset exists.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned
     *
     * @since 5.0.0
     */
    public function offsetExists($offset) : bool
    {
        if (isset($this->values[$offset])) {
            return true;
        }

        $keys = explode($this->keySeparator, $offset);
        $value = $this->values;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return false;
            }

            $value = &$value[$key];
        }

        return true;
    }

    /**
     * Offset to retrieve.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if (isset($this->values[$offset])) {
            return $this->values[$offset];
        }

        $keys = explode($this->keySeparator, $offset);

        $value = $this->values;

        foreach ($keys as $key) {
            $value = &$value[$key];

            if ($value === null) {
                break;
            }
        }

        return $value;
    }

    /**
     * Offset to set.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->values[] = $value;

            return;
        }

        $keys = explode($this->keySeparator, $offset);

        $keyCount = count($keys);

        $array = &$this->values;

        while ($keyCount > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];

            --$keyCount;
        }

        $array[array_shift($keys)] = $value;
    }

    /**
     * Offset to unset.
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $keys = explode($this->keySeparator, $offset);

        $keyCount = count($keys);

        $array = &$this->values;

        while ($keyCount > 1) {
            $key = array_shift($keys);

            if (isset($array[$key]) && is_array($array[$key])) {
                $array = &$array[$key];
            }

            --$keyCount;
        }

        unset($array[array_shift($keys)]);
    }
}
