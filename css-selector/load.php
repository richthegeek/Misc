<?php

require "tidyPQ.php";

error_reporting( E_ALL );

$url = explode( "/", $_GET['url'] );
array_pop( $url );
$curl = implode( "/", $url );
array_pop( $url );
$curl2 = implode( "/", $url );

if( $curl2 == "http:" ) $curl2 = $_GET['url'];

$pq = new tidyPQ( $_GET['url'] );

pq( "script, meta, title" )->remove();

$links = pq( "link" );

foreach( $links as $link )
{
	$href = pq( $link )->attr( "href" );
	pq( "<link rel='stylesheet' href='".$curl2."/".$href."'/>" )->appendTo("head");
}

$data = $pq->asHTML();

$data = str_replace( "@import url(\"/", "@import url( \"".$curl2."/", $data );
$data = str_replace( "@import url(\"", "@import url(\"".$curl."/", $data );


print $data;

?>
