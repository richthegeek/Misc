<?php

class Loader
{
	function Loader( $system = false, $application = false )
	{
		$this->sysdir = $system;
		$this->appdir = $application;
	}


	function view( $name, $data, $return = false )
	{
		$path = $this->appdir . "/views/" . $name . ".php";
		if( ! file_exists( $path ) )
		{
			throw new Exception( "MMVC error: Unable to locate requested view file $name under $path.");
			return false;
		}

		# enable output buffering so we can return if required
		ob_start();

		# make the data array globalised: array('hello'=>'goodbye') is turned into $hello = "goodbye"
		extract( $data ); # http://uk.php.net/extract

		# require the file (not require_once as same file may be included multiple times)
		try {
			require( $path );
		} catch (Exception $e) {
			throw new Exception( "MMVC Error: Unable to load $name.php view file (possible overwritten function/classname, or PHP error)." );
		}

		# return or output - check the docs for more detailed info on the OB functions
		if( $return )
		{
			$c = ob_get_contents();
			ob_end_clean();
			return $c;
		}
		else return ob_end_flush();
	}


	function helper( $name )
	{
		$name = ucwords($name);

		# only needs to load the file, not check classes
		try
		{
			require_once( $this->appdir . "/helpers/" . $name . ".php" );
		}
		catch (Exception $e)
		{
			throw new Exception( "MMVC Error: Unable to load $name.php helpers file.");
		}
	}

	# this handles the specific loading for libraries, models, and the controller
	function load( $name, $path, $alias = false )
	{
		# if the file doesn't exist, throw an exception and return.
		if( ! file_exists( $path ) )
		{
			throw new Exception( "MMVC error: Unable to locate requested load file $name under $path.");
			return false;    
		}

		# ucwords (capitalize) to form the class name
		# NOTE! This means that all class names must be [A-Z][A-Za-z0-9_]+ (Hello valid, hello invalid)
		$cname = ucwords($name);

		# attempt to load (require_once) the file and init the class
		try {
			require_once( $path );
			$obj = new $cname($this->BASE);
		} catch (Exception $e) {
			throw new Exception( "MMVC: Unable to load class ".$cname." from $path." );
			return false;
		}

		# if it worked we get to here, and assign the object to the base
		if( ! $alias ) $alias = strtolower($name);
		$this->BASE->$alias = $obj;    
	}

	# the library method allows for multiple libs to be loaded at once, others do not
	function library( $a ) /* $a enforces min number of args (1..inf) */
	{
		foreach( func_get_args() as $arg )
			$this->load( $arg, $this->sysdir."/".$arg.".php" );
	}

	function controller( $name, $alias = false ) /* $name is the actual file/class name, alias allows shortening of names (eg User_model -> user). */
	{
		$this->load( $name, $this->appdir."/controllers/".$name.".php", $alias );
	}

	function model( $name, $alias = false ) /* see controller */
	{     
		$this->load( $name, $this->appdir."/models/".$name.".php", $alias );
	}

}