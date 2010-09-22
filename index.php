<?php
/**
 * Main index
 */

require 'bootstrap.php';

#
# Get current page
#
$page = DMS_System_Service::get('p','GET');
$page = (!empty($page))? $page : DMS_System_Settings::getPublic('site_homepage');

#
# Template
#
$template = new DMS_System_Template( new DMS_System_Settings() );

# Load templates
try {
	$template->load($page.'.php');
}
catch( DMS_Exceptions_FileNotExists $e) {
	DMS_System_Service::errorPage(404);
}
$template->render();



