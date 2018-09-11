<?php

namespace  IMooc;
class Config implements \ArrayAccess
{
    protected $path;
    protected $configs=array();
    function __construct($path)
    {
        $this->path = $path;
    }

    function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        if(empty($this->configs[$offset])){
            $file_path = $this->path."/".$offset.".php";
            $config = require $file_path;
            $this->configs[$offset] = $config;
        }
        return  $this->configs[$offset];
    }

    function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
    function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }
    function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.

    }
}