<?php
namespace GlobalE\SDK\Models;

abstract class Common {

    /**
     * Set/Get attribute wrapper
     * This method will be called each time somebody will try to use method that doesn't exist in the common,
     * for cases when the method will begin by "set" or "get" it will set or get accordingly property in the common.
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = substr($method,3);
                $data = isset($this->{$key}) ? $this->{$key} : null;
                return $data;

            case 'set' :
                $key = substr($method,3);
                $data = isset($args[0]) ? $args[0] : null;
                $this->{$key} = $data;
                return $this;
        }
        return null;
    }

    /**
     * This method will be called each time common object will be used as string,
     * it will return the object as JSON.
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * This method will be called each time somebody tries to use static method on common that doesn't exist,
     * the method will return null.
     * @param $method
     * @param $args
     * @return null
     */
    public static function __callStatic($method, $args)
    {
        return null;
    }
}