<?php
/**
 * DMS Bootstrap script
 */

#
# Includes
#
require 'lib/dms/dms.class.php';
require 'lib/dms/pdo.class.php';
require 'lib/dms/settings.class.php';
require 'lib/dms/service.class.php';
require 'lib/dms/template.class.php';
require 'lib/dms/user.class.php';
// require 'lib/dms/profile.class.php';

require 'lib/dll.class.php';

#
# Database settings
#
DMS_System_Settings::setProtected('pdo_host','localhost');
DMS_System_Settings::setProtected('pdo_database','test_dms');
DMS_System_Settings::setProtected('pdo_user','root');
DMS_System_Settings::setProtected('pdo_pass','AEeegKDzk');

#
# Public settings
#
DMS_System_Settings::setPublic('site_url','http://localhost/~tom/dms/');
DMS_System_Settings::setPublic('site_template','default');
DMS_System_Settings::setPublic('site_enable_gzip',false);
DMS_System_Settings::setPublic('site_main_title','DMS System');
DMS_System_Settings::setPublic('site_page_title','Default Title');
DMS_System_Settings::setPublic('site_homepage','home');
DMS_System_Settings::setPublic('site_enable_user', true);

DMS_System_Settings::setPublic('site_include_jqueryui',true);

DMS_System_Settings::setPublic('profile_registration_active',false);
DMS_System_Settings::setPublic('user_email_login', false);




#
# Register Services
#
DMS_System_Service::register('login',array('DMS_User_Service','login'));
DMS_System_Service::register('logout',array('DMS_User_Service','logout'));

#
# Enable Sessions
# (If user system is enabled; redirect if not logged in.)
#
if(DMS_System_Settings::getPublic('site_enable_user')) {
	$sess = DMS_User_Factory::getSession();
	$page = DMS_System_Service::get('p','GET');
	$action = DMS_System_Service::get('action');
	
	if(!$sess->user->id && $page!='login' && $action!='login' && $action!='logout')
		DMS_System_Service::redirect('?p=login');
}

define('BOOTSTRAP', true);