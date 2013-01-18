<?php

namespace Emakina\Component\ArrayAccess;


/**
 * AbstractEntity.
 *
 */
abstract class AbstractArrayAccess implements \ArrayAccess
{
    private $properties;


    public function __construct()
    {
        $this->properties = array();
        $this->configure();
    }

    public function addProperty($name, $default = null)
    {
        $this->properties[$name] = $default;

        return $this;
    }

    public function __call($name, $arguments)
    {
        $method = substr($name, 0, 3);
        $property = lcfirst(substr($name, 3));

        if ('set' === $method) {
            if (!array_key_exists(0, $arguments)) {
                throw new \LogicException(sprintf('Please provide a value to set %s with', $name));
            }
            $this->set($property, $arguments[0]);
        } elseif ('get' === $method) {
            return $this->get($property);
        } elseif ('add' === $method) {
            $this->add($property, $arguments[0]);
        }
    }

    public function set($property, $value)
    {
        if (!array_key_exists($property, $this->properties)) {
            throw new \LogicException(sprintf('Property %s is not present in instance of "%s".', $property, get_class($this)));
        }

        $this->properties[$property] = $value;

        return $this;
    }

    public function get($property)
    {
        if (!array_key_exists($property, $this->properties)) {
            throw new \LogicException(sprintf('Property %s is not present in instance of "%s".', $property, get_class($this)));
        }

        return $this->properties[$property];
    }

    public function has($property)
    {
        return array_key_exists($property, $this->properties);
    }

    public function add($property, $value)
    {
        if (!array_key_exists($property, $this->properties)) {
            throw new \LogicException(sprintf('Property "%s" is not present in instance of "%s".', $property, get_class($this)));
        }

        $this->properties[$property][] = $value;
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->properties);
    }

    public function offsetGet($index)
    {
        return $this->get($index);
    }

    public function offsetSet($index, $value)
    {
        $this->set($index, $value);
    }

    public function offsetUnset($index)
    {
        throw new \BadMethodCallException('Not available.');
    }

    abstract protected function configure();
    abstract public function transform(array $data);
}
