<?php

require "tidyPQ.php";

$url = isset( $_GET['url'] ) ? $_GET['url'] : "http://www.jkg3.com/";

$pq = new tidyPQ( $url );

print $pq->asHTML();

?>
