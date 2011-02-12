<?php

class Ufile extends Model
{
	var $base = "";

	function Ufile()
	{
		Model::Model();
	
		$this->base = $_SERVER['DOCUMENT_ROOT']."gaphor/files/";
	}

	function add_user( $username )
	{
		if( !file_exists( $this->base.$username ) )
		{
			mkdir( $this->base.$username );
		}
	}
	
	function add_project( $project )
	{
		$username = $this->username();
		if( !file_exists( $this->base.$username."/".$project ) )
		{
			mkdir( $this->base.$username."/".$project );
		}	
	}
	
	function rm_project( $username, $project )
	{
		delete_files( "files/$username/$project", TRUE );
		rmdir( $this->base.$username."/".$project );
	}
	
	function rm_file( $username, $project, $file )
	{
		unlink( $this->base.$username."/".$project."/".$file );
	}
	
	function rm_user( $username )
	{
		delete_files( "files/$username", TRUE );
		rmdir( $this->base.$username );
		$this->session->unset_userdata( "username" );
	}
	
	function list_projects( $username = false )
	{
		if( !$username ) $username = $this->username();
		
		return $this->get_dirs( $username, $this->base.$username );
	}
	
	function list_files( $username, $project )
	{
		$files = get_filenames( "files/$username/$project" );		
		return $files;
	}
	
	function get_dirs( $u, $dir )
	{
		$handle = opendir( $dir );
		
		$fl = array();
		
		while( false !== ( $file = readdir( $handle ) ) )
		{
			if( $file != "." && $file != ".." )
			{
				$fl[$file] = $this->list_files( $u, $file );
			}
		}
		return $fl;
	}
	
	function get_file( $u, $p, $f )
	{
		return "<pre>".file_get_contents( $this->base.$u."/".$p."/".$f )."</pre>";
	}
	
	function username( $name = false )
	{
		if( !$name ) {
			return $this->session->userdata( 'username' );
		} else {
			return $this->session->set_userdata( 'username', $name );
		}
	}
}

?>
