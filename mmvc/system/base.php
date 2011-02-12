<?php

# this class is designed to act as a singleton - that is, only one object of type Base will ever be created per execution, and
# any requests for Base will return that object, so any attributes assigned to the singleton will be available to all references

class Base
{
	private static $instance;														# part of the singleton setup

	private $sysdir = false;
	var $appdir = false;
	var $subclasses = array();														# stores all the subclasses (config, uri, router, load, etc)

	function Base( $system = false, $application = false )
	{
		$this->sysdir = $system;
		$this->appdir = $application;
	}

	public static function get_instance( $system = false, $application = false )	# used to get a singleton
	{
		if( ! self::$instance )														# if the instance is not already set, create it
		{
			$c = __CLASS__;                 										# __CLASS__ predefined class constant
			self::$instance = new $c( $system, $application );						# and init - note odd syntax :: instead of ->
		}

		return self::$instance;             										# return the singleton instance - because of the statics all over, this is always the same object
	}

	public function appdir()
	{
		return $this->appdir;
	}
	public function sysdir()
	{
		return $this->sysdir;
	}

	public function __set( $name, $value )  										# magic method __set (http://www.php.net/manual/en/language.oop5.magic.php)
	{
		if( isset($this->subclasses[$name]) ) 										# throw instead of die error, allows catching if error is non-fatal
			throw new Exception( "MMVC Error: Attempting to re-assign subclass $name." );
		
		$this->subclasses[$name] = $value;
	}

	public function __get( $name )  												# see __set for link
	{
		if( ! isset( $this->subclasses[$name]) ) 									# same again, throw to allow non-fatal error handling
			throw new Exception( "MMVC Error: Attempting to access undefined subclass $name." );
		
		# if the requested entity is an object, and the BASE var is not already set, set it to the base instance
		if( is_object($this->subclasses[$name]) && !isset( $this->subclasses[$name]->BASE ) )
			$this->subclasses[$name]->BASE = self::$instance;

		return $this->subclasses[$name];
	}

	public function __isset( $name )
	{
		return isset( $this->subclasses[$name] ) || isset( $this->$name );
	}
}