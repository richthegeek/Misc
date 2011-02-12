<?php

# connects nodes to form a clique size K in $graph

function k_graph( & $graph, $k, $s = array() )
{
	// select random node
	// ensure node has not been previously selected
	
	// if k = 1
	//	return graph, 

	$keys = array_keys( $graph->nodes );
	$c = count( $keys ) - 1;
	
	$r = $keys[ rand( 0, $c ) ];
	
	while( isset( $s[ $r ] ) ) $r = $keys[ rand( 0, $c ) ];
	
	$s[ $r ] = $r;
	
	if( $k == 1 )
		return $s;
	
	$s = k_graph( $graph, $k - 1, $s );
	
	foreach( $s as $nn=>$bla ) $graph->connect( $r, $nn );
	
	return $s;	 
}
