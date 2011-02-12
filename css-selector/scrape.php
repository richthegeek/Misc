<?php

mysql_connect( "localhost", "root", "root" );
mysql_query( "use scraper" );

require "tidyPQ.php";

$nextq = mysql_query("SELECT * FROM parse_queue WHERE scraped = 0 ORDER BY id ASC LIMIT 1");

if( mysql_num_rows( $nextq ) == 0 )
{
	$queue = array();
	$query = mysql_query( "SELECT id FROM sets" );
	while( $row = mysql_fetch_object( $query ) )
	{
		$set = $row->id;
		$rules = get_rules ( $set );
		
		foreach( $rules as $rule )
		{
			// only base rules, not child rules
			if( isset( $rule->rule->next_rule ) ) unset( $rules[ $rule->rule->next_rule ] );
		}
		foreach( $rules as $rule )
		{
			print "fill queue ";
			enqueue( $rule->set, $rule->id );
		}
	}

}
else
{
	$next = mysql_fetch_object( $nextq );
	$rules = get_rules( $next->set );
	$rule = $rules[ $next->rule ];
	$data = strlen( $next->data ) > 0 ? json_decode( stripslashes( $next->data ) ) : false;
	parse_rule( $rules, $rule, $data );
	$query = mysql_query( "UPDATE parse_queue SET scraped = 1 WHERE `id` = ".$next->id );
}


function enqueue( $set, $rule, $data = false )
{
	print "enqueue $set $rule $data<br>";

	$query = "INSERT INTO parse_queue SET `scraped` = 0, `set` = $set, `rule` = $rule";

	if( $data ) $query .= ", `data` = '".addslashes(json_encode( $data ))."'";
	
	mysql_query( $query ) or die( mysql_error() );
}


function in_queue( $set, $rule, $data )
{
	$query = mysql_query("SELECT * FROM parse_queue WHERE `set` = $set AND  `rule` = $rule AND `data` = '".addslashes(json_encode( $data ))."'");
	return (boolean)mysql_num_rows( $query );
}

function store_result( $set, $rule, $data )
{
	$query = "INSERT INTO review_queue SET `set` = $set, `rule` = $rule, `result` = '".addslashes(json_encode( $data ))."'";
	mysql_query( $query ) or die( mysql_error() );
}

function get_rules( $set )
{
	$query = mysql_query( "SELECT * FROM `rules` WHERE `set` = $set" );
	$rules = array();
	while( $rule = mysql_fetch_object( $query ) )
	{
		$rule->rule = json_decode( $rule->rule );
		$rid = $rule->rule->rule;
		while( isset( $rules[$rid] ) ) $rid++;	
		$rules[ $rid ] = $rule;
	}
	ksort( $rules );
	return $rules;
}




function parse_rule( $rules, $rule, $data = false )
{
	if( $rule->rule->type == "follow_links" )
	{
		$url = $rule->rule->url;

		$pq = new tidyPQ( $url );

		$links = pq( $rule->rule->selector );
		
		$results = array();
		
		foreach( $links as $link )
		{
			$results[] = stripslashes( pq($link)->attr("href") );
			print "parse rule ";
			if( !in_queue( $rule->set, $rule->rule->next_rule, array( stripslashes( pq($link)->attr("href") ) ) ) )
				enqueue( $rule->set, $rule->rule->next_rule, array( stripslashes( pq($link)->attr("href") ) ) );
		}
		
		
		//parse_rule( $rules, $rules[ $rule->rule->next_rule ], $results );
	}
	else if( $rule->rule->type == "get_data" && $data )
	{
		foreach( $data as $url )
		{
			if( substr($url,0,strlen($rule->rule->url)) == $rule->rule->url )
			{
				$pq = new tidyPQ( $url );
				
				$results = array();
				
				foreach( $rule->rule->selectors as $target=>$selector )
				{
					$sel = substr( $selector, 0, strpos( $selector, "%" ) );
					$get = substr( $selector, strpos( $selector, "%" ) + 1 );
										
					//$result = pq( $sel );
					
					if( $get == "text" )
					{
						$result = pq( $sel )->text();
					}
					elseif( substr( $get, 0, 5 ) == "attr=" )
					{
						$result = pq( $sel )->attr( substr( $get, 5 ) );
					}
					
					$results[ $target ] = $result;
				}
				
				store_result( $rule->set, $rule->id, $results );
				
			}
			else
			{
				store_result( $rule->set, $rule->id, $url );
			}
		}
	}
}
