<?php

namespace Entities\Core;

class AbstractEntity
{
    public function __get($key)
    {
        return $this->$key;
    }

    public function __set($key, $val)
    {
        $this->$key = $val;
    }

    public function __call($name, $args)
    {
        if (strpos($name, 'set') === 0) {
            $key = self::camelToUnder(substr($this->$name, 0, 3));
            $val = $args[0];
            $this->$key = $val;
        } else {
            $key = self::camelToUnder(substr($this->$name, 0, 3));
            return $this->$key;
        }
    }

    public function setData($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
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
}
