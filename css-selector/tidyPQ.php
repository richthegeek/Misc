<?php

require "phpQuery.php";

class tidyPQ
{
	function tidyPQ( $url = false, $html = false )
	{
		if( $url ) return $this->load( $url, $html );
	}
	
	function load( $string, $html = false )
	{
		if( !$html ) return $this->load_url(  $string );
		if(  $html ) return $this->load_html( $string );	
	}

	function load_url( $url )
	{
		$html = file_get_contents( $url );
		return $this->load_html( $html );
	}
	
	function load_html( $html )
	{
		$tidy = tidy_parse_string( $html );
			tidy_clean_repair( $tidy );
		$html = tidy_get_html( $tidy );
		
		phpQuery::unloadDocuments();
		
		return phpQuery::newDocumentHTML( $html );		
	}
	
	function _( $selector )
	{
		return pq( $selector );	
	}
	
	function asHTML()
	{
		return pq(":first")->htmlOuter();
	}
}

?>
