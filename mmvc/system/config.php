<?php

class Config extends Extender
{

	var $data = array();

	function init()
	{
		try
		{
			$file = file_get_contents( $this->appdir() . "/config/manifest.json" );
			$json = json_decode( $file );
		}
		catch (Exception $e)
		{
			die($e);
		}

		foreach( array_merge($json->JSON,$json->PHP) as $j )
			$this->load( $j );
	}

	function load( $file )
	{
		try
		{
			$data = $this->load_json( $file );
			if( ! $data ) $data = $this->load_php( $file );
		}
		catch (Exception $e)
		{
			throw $e;
			return false;
		}

		return $data;
	}

	function load_json( $file )
	{
		$path = $this->appdir . "/config/$file.json";

		try {
			$data = json_decode( @file_get_contents( $path ) );
		} catch (Exception $e) {
			throw new Exception( "Unable to load config JSON file $file.json" );
			return false;
		}

		$this->data[ $file ] = $data;
		return $data;
	}

	function load_php( $file )
	{
		$path = $this->appdir . "/config/$file.php";
		
		try {
			@require_once( $path );
			$data = $$file;
		} catch (Exception $e) {
			throw new Exception( "Unable to load config PHP file $file.php" );
			return false;
		}

		$this->data[ $file ] = $data;
		return $data;
	}

	function get( $nameset, $name = false )
	{
		if( ! $name )
			list( $nameset, $name ) = explode(".",$nameset,2);

		if( isset( $this->data[$nameset]) )
			if( is_array($this->data[$nameset]) && isset($this->data[$nameset][$name]) )
				return $this->data[$nameset][$name];
			else if( is_object($this->data[$nameset]) && isset($this->data[$nameset]->$name) )
				return $this->data[$nameset]->$name;
			else
				return $this->data[$nameset];
		else
			throw new Exception("MMVC Error: Unable to load config item $nameset.$name");
		return false;
	}
}