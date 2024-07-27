<?php

namespace TildaTools\Tilda\Objects;


use BadMethodCallException;
use JsonSerializable;

class BaseObject implements JsonSerializable
{

    protected $attributes;

    /**
     * BaseObject constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function get($key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }

    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->$name($arguments);
        }
        throw new BadMethodCallException();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->attributes;
    }
}
