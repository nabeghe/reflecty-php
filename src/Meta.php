<?php /** @noinspection PhpLanguageLevelInspection */

namespace Nabeghe\Reflecty;

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

    public function all(): array
    {
        return $this->_;
    }

    public function update(array $_)
    {
        $this->_ = $_;
    }

    public function merge(array $_)
    {
        $this->_ = array_merge($this->_, $_);
    }
}