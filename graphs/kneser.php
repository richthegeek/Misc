<?php

# enumerate a kneser graph K(n,k), can be rewritten to use the banker's sequence enumerations

function kneser( $graph, $n, $k )
{
	if( $n <= 0 ) die( "n must be positive" );
	if( ! ($k > 0 && $k <= $n ) ) die( "k must be positive and less than n" );
	
	$s = subsets( $n, $k );
	
	for( $i = 0; $i < count($s); $i++ )
	{	
		$set = $s[$i];
		for( $j = $i + 1; $j < count($s); $j++ )
		{
			$can = $s[$j];
			if( disjoint( $set, $can ) )
				$graph->connect( implode("",$set), implode("",$can) );
		}
	}
}

function disjoint( $a, $b )
{
	$d = true;
	foreach( $a as $i )
		foreach( $b as $j )
			if( $i == $j )
				return false;
	
	return true;
}

function subsets( $n, $k )
{
	$size = 1;
	$set = array();
	$lpos = 0;

	for( $i	= 0; $i < $n; $i++ )
		$set[] = array( $i + 1 );
		
	while( $size <= $k )
	{
		$l = count( $set );
		for( $i = $lpos; $i < $l; $i++ )
		{
			for( $j = 0; $j < $n; $j++ )
				$set[] = array_merge($set[$i],array($j));
		}
		
		$lpos = $l;
		$size++;
	}
	
	foreach( $set as $s )
		print implode("",$s)."\n";
	
	die;
}

