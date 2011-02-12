<?php

define('INDEX_PATH',__FILE__);                              # potentially useful later

$system = "./system";                                       # allows the system folder to be moved
$application = "./application";                              # allows the application folder to be moved

require( $system . "/base.php");                            # include the base class (./system/base.php) and the Extender (base shortener for subclasses)
require( $system . "/extender.php");
require( $system . "/loader.php");                          # include the load class (./system/loader.php)

$base = Base::get_instance( $system, $application );        # load the base class as singleton (http://www.php.net/manual/en/language.oop5.patterns.php#language.oop5.patterns.singleton)

$base->load = new Loader( $system, $application );          # init the load class to base->load (see Base::__set)

#$base->load->library("config","uri","router","input");      # use the load class to get the remaining libraries

$base->load->library("config");

$base->config->init();


$base->load->controller( "welcome" );

$base->welcome->bob();