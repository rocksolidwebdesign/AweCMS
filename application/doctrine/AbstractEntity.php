<?php

namespace Entities;

class AbstractEntity
{
    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($key, $val)
    {
        $this->$key = $val;
    }

    public function __call($name, $args)
    {
        if (strpos($name, 'set_') === 0)
        {
            $key = substr($this->$name, 0, 4);
            $this->$name = $val;
        }
        else
        {
            $key = substr($this->$name, 0, 4);
            return $this->$name;
        }
    }
}
