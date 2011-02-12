<?php

class Extender
{
    function __get( $name )
    {
        $ret = isset( $this->$name ) ? $this->name : isset( $this->BASE->$name ) ? $this->BASE->$name : false;

        if( ! $ret && $name == "BASE" ) return Base::get_instance();

        if( ! $ret )
            throw new Exception( "MMVC Error: attempt to access undefined attribute $name");
        
        return $ret;
    }

    function __call( $name, $arguments )
    {
    	if( method_exists( $this->BASE, $name ) )
    		return call_user_func_array( array( $this->BASE, $name ), $arguments );
    }
}