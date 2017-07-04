<?php

namespace Pure360;

trait GetAndSet
{
    public function __call($name, $arguments)
    {
        $class = get_class($this);
        $type = substr($name,0,3);
        $property = lcfirst(substr($name,3));

        if (property_exists($this, $property)) {
            switch ($type)
            {
                case 'get':
                    $result = $this->$property;
                    break;
                case 'set':
                    $this->$property = $arguments[0];
                    $result = $this;
                    break;
                default:
                    throw new \Exception("Method {$class}::{$name} is not defined!");
            }
        } else {
            throw new \Exception("Method {$class}::{$name} is not defined!");
        }
        return $result;
    }

}
