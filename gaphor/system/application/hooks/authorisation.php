<?php

class Authorisation
{
	var $CI;

	function auth_all()
	{
		$this->CI =& get_instance();		
		$section = $this->CI->uri->segment( 1 );
		$page    = $this->CI->uri->segment( 2 );
		
		$location = "$section/$page";
		
		if( !$this->CI->session->userdata( 'username' )
			&& $location != "jtog/login"
			&& $location != "jtog/logout" )
		{
			redirect( "jtog/login" );
		}
	}
	
}
