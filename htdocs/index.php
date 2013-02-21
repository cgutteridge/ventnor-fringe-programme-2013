<?php

require_once( "../lib/arc2/ARC2.php" );
require_once( "../lib/Graphite/Graphite.php" );
date_default_timezone_set( "Europe/London" );

$f3=require('lib/base.php');

// Initialize CMS
$f3->config('app/config.ini');

// Define routes
$f3->config('app/routes.ini');

// Execute application
$f3->run();

exit;

