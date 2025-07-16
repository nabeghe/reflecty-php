<?php /** @noinspection PhpLanguageLevelInspection */

namespace Nabeghe\Reflecty;

/**
 * A general attribute that can be used in any class and contains that class's configuration settings.
 *
 * This attribute can be read and utilized using `Reflecty::meta`.
 */
#[\Attribute]
class Meta implements \ArrayAccess, \JsonSerializable
{
    protected array $_ = [];

    public function __construct(array $_ = [])
    {
        $this->_ = $_;
    }

    public function __get(string $name)
    {
        return $this->_[$name] ?? null;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->_[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->_[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        if (array_key_exists($offset, $this->_)) {
            unset($this->_[$offset]);
        }
    }

    public function jsonSerialize(): array
    {
        return $this->_;
    }

    /**
     * Returns all values.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->_;
    }

    /**
     * Updates all values.
     *
     * @param  array  $_
     * @return void
     */
    public function update(array $_)
    {
        $this->_ = $_;
    }

    /**
     * Returns the value of a given path using dot notation.
     *
     * @param  string  $path
     * @return array|mixed|null
     */
    public function get(string $path)
    {
        $keys = explode('.', $path);
        $meta = &$this->_;

        foreach ($keys as $key) {
            if (isset($meta[$key])) {
                $meta = &$meta[$key];
            } else {
                return null;
            }
        }

        return $meta;
    }

    /**
     * Updates the value of a given path using dot notation.
     *
     * @param  string  $path
     * @param $value
     * @return void
     */
    public function set(string $path, $value)
    {
        $keys = explode('.', $path);
        $meta = &$this->_;

        foreach ($keys as $key) {
            if (!isset($meta[$key])) {
                $meta[$key] = [];
            }

            $meta = &$meta[$key];
        }

        $meta = $value;
    }

    /**
     * Merges new values into the existing ones.
     *
     * @param array $_ New values to merge.
     * @param bool $deep Optional. Whether to recursively merge nested arrays. Default is false.
     * @param bool $overwrite Optional. If true, existing keys will be overwritten by new values. Default is true.
     * @return void
     */
    public function merge(array $_, bool $deep = false, bool $overwrite = true)
    {
        if ($deep) {
            $this->_ = static::deepMege($this->_, $_, $overwrite);
        } else {
            if ($overwrite) {
                $this->_ = array_merge($this->_, $_);
            } else {
                foreach ($_ as $name => $value) {
                    if (!isset($this->_[$name])) {
                        $this->_[$name] = $value;
                    }
                }
            }
        }
    }

    /**
     * Recursively merges two arrays.
     *
     * @param array $a The base array to merge into.
     * @param array $b The array with new values to merge from.
     * @param bool $overwrite Optional. Whether to overwrite existing keys in $a with values from $b. Default true.
     * @return array The merged array.
     */
    protected static function deepMege(array &$a, array &$b, bool $overwrite = true): array
    {
        $merged = $a;

        foreach ($b as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = static::deepMege($merged[$key], $value);
            } elseif ($overwrite || !isset($merged[$key])) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}