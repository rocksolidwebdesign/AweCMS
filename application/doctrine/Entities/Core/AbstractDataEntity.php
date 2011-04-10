<?php

namespace Entities\Core;

class AbstractEntity
{
    private $data = array();

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function __set($key, $val)
    {
        $this->_data[$key] = $val;
    }

    public function __call($name, $args)
    {
        if (strpos($name, 'set') === 0)
        {
            $key = self::camelToUnder(substr($this->$name, 0, 3));
            $key = lcfirst(substr($this->$name, 0, 3));
            $val = $args[0];
            $this->_data[$key] = $val;
        }
        else
        {
            $key = self::camelToUnder(substr($this->$name, 0, 3));
            $key = lcfirst(substr($this->$name, 0, 3));
            return $this->_data[$key];
        }
    }

    public function setData($data)
    {
        foreach ($data as $key => $val) {
            $this->_data[$key] = $val;
        }
    }

    protected static function camelToUnder($str)
    {
        $new = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++ ) {
            if (ctype_upper($str{$i})) {
                $new .= '_'.strtolower($str{$i});
            } else {
                $new .= $str{$i};
            }
        }

        return $new;
    }

    protected static function underToCamel($str, $ucfirst = false) {
        $new = implode('', array_map('ucfirst', explode('_', $str)));

        if (!$ucfirst) {
            $new = lcfirst($new);
        }

        return $new;
    }

    /*
    // Provided for backwards compatibility
    protected static function lowerFirst($str) {
        return strtolower($str{1}).substr($str, 1);
    }

    protected static function underToCamelLCF($str, $ucfirst = false) {
        $new = implode('', array_map('ucfirst', explode('_', $str)));

        if (!$ucfirst) {
            $new = self::lowerFirst($new);
        }

        return $new;
    }
    */
}
