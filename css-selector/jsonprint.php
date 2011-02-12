<?php

$o = new stdClass;

$o->rule = 20;
$o->url = "http:\/\/www.iqpc.com\/ShowEvent\.aspx\?id=[0-9]{0,20}";
$o->type = "get_data";
$o->selectors = new stdClass;

$o->selectors->Title = "span.headline1_font %text";
$o->selectors->Image = "div#eventicon > a > img %attr=src";

print json_encode( $o );
