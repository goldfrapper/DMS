<?php
/**
 * Service index
 */

if(!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
	header('HTTP/1.1 500 Illegal Service'); exit();
} else $action = $_REQUEST['action'];

require 'bootstrap.php';

try {
	DMS_System_Service::call( $action );
} catch( DMS_Exceptions_IllegalService $e ) {
	DMS_System_Service::errorPage(400);
} catch( Exception $e ) {
	DMS_System_Service::errorPage(500);
}

# Redirect anyway
DMS_System_Service::redirect();