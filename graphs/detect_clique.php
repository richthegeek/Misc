<?php

# rudimentary clique-detection algorithm (lists all maximum cliques)
# worst case is graphs of 75% density, but /can/ run with relatively small orders (n^3)
# under "real world" circumstances

function shuffle_assoc($list) {
  if (!is_array($list)) return $list;

  $keys = array_keys($list);
  shuffle($keys);
  $random = array();
  foreach ($keys as $key)
    $random[$key] = $list[$key];

  return $random;
}

$c1 = 0;
$c2 = 0;
$searched = array();

function detect_cliques( $graph )
{
	global $c1, $c2, $searched, $s, $k;
	
	$max = 0;
	$cliques = array();
	$incl = array();

	list( $graph, $max, $cliques, $incl ) = iterate( $graph, $graph->nodes, $max, $cliques );

	foreach( $cliques as $i=>$c )
		if( count($c) == $max )
			$largest = $c;

	return array( $max, $cliques, $largest );
}

function iterate( $graph, $nodes, $max, $cliques )
{
	global $c1, $c2, $s, $k;

	// for every node in the graph as node1
	foreach( $nodes as $key1 => $node1 )
	{
		$c1++; $c2++;
		
		// for every node joined to node1 as node2
		foreach( $node1->nodes as $key2 => $null )
		{
			$c1++; 
			// if node1 and node2 are currently in a clique together, skip to next node2
			if( isset($incl[$key2]) && isset($incl[$key2][$key1]) )
			{
				continue;
			}
			$c2++;
			
			$node2 = $graph->nodes[ $key2 ];
			
			// clique <- [ node1, node2 ]
			$clique = array( $key1=>$node1, $key2=>$node2 );
						
			$c = 0;
			
			// c <- 0
			// while c < |clique|
			//	c = |clique|
			//	clique = expand_clique( clique )
			// ewhile
			while( $c != count( $clique ) )
			{
				$c1++; $c2++;
				$c = count( $clique );				
				$clique = expand( $graph, $clique );
			}
			
			if( count( $clique ) < 2 ) continue;
			
			$cliques[] = $clique;
			$max = max( $max, count($clique) );
			
			//if( count( $clique ) < 3 ) continue;
			
			foreach( $clique as $ke=>$v ) $incl[ $ke ] = $clique;
		}
	}
	return array( $graph, $max, $cliques, $incl );
}

function expand( $graph, $clique )
{
	global $c1, $c2, $searched;
	
	$checked = array();
	$common = $clique;
		
	foreach( $clique as $key1=>$node1 )
	{
		$c1++; $c2++;
		foreach( $node1->nodes as $key2 => $null )
		{
			$searched[ $key2 ] = true;
			$node2 = $graph->nodes[ $key2 ];
			
			$c1++;
			if( isset( $common[ $key2 ] ) || isset( $checked[ $key2 ] ) ) continue;
			$c2++;
			
			$count = 0;			
			foreach( $common as $key3=>$null )
				if( isset( $graph->nodes[$key3]->nodes[$key2] ) || $key2 == $key3 )
					$count++;
		
			if( $count >= count( $common ) )
				$common[ $key2 ] = $node2;
			
			$checked[ $key2 ] = true;
		}
		break;
	}
	
	return $common;
}
