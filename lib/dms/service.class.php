<?php
/**
 * DMS Service System
 */

class DMS_Exceptions_IllegalService extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_ServiceError extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_NoServiceGiven extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_IllegalServiceMethod extends DMS_Exceptions_DefaultException {}


/**
 *
 */
class DMS_System_Service
{
	static private $actions = array();
	static private $httpvars = array();
	
	/**
	 * Register a service callback
	 *
	 * TODO: Callbacks should be verified at this stage.
	 * @param string name
	 * @param mixed callback pseudo-type
	 * @see http://be.php.net/manual/en/language.pseudo-types.php#language.types.callback
	 */
	static public function register( $name, $callback )
	{
		if(is_string($callback) && !function_exists($callback)) {
			throw new DMS_Exceptions_IllegalService();
		}
		elseif(is_array($callback)) {
			 if(!class_exists($callback[0]))
				throw new DMS_Exceptions_IllegalService();
			 if(!method_exists($callback[0],$callback[1]))
				throw new DMS_Exceptions_IllegalServiceMethod();
		} else {
			throw new DMS_Exceptions_NoServiceGiven();
		}
		self::$actions[$name] = $callback;
	}
	
	static public function call( $name )
	{
		if(!isset(self::$actions[$name])) {
			throw new DMS_Exceptions_IllegalService();
		}
		try {
			call_user_func_array( self::$actions[$name], array() );
		} catch( Exception $e) {
			throw new DMS_Exceptions_ServiceError();
		}
	}
	
	/**
	 * Get HTTP request variables
	 *
	 * TODO: Sanitize the data before returning.
	 *
	 * @param string parameter name
	 * @param string method - defaults to $_REQUEST
	 * @param string default output is !isset - defaults to null
	 */
	static function get( $param, $method = 'REQUEST', $default = null )
	{
		if(empty(self::$httpvars)) self::_overwriteMainSymbolTable();
		
		if(!isset( self::$httpvars['_'.$method] ) || !isset(self::$httpvars['_'.$method][$param]))
			return $default;
		else {
			$var = self::$httpvars['_'.$method][$param];
			if(is_numeric($var)) $var = (int)$var;
			return $var;
		}
	}
	
	/**
	 * In this function the main sybol table should get overwritten
	 * and hardened.
	 * @see http://www.hardened-php.net/globals-problem
	 */
	static private function _overwriteMainSymbolTable()
	{
		self::$httpvars = $GLOBALS;
	}
	
	/**
	 * HTTP Redirect function
	 */
	static function redirect( $location = '', $code = 303 )
	{
		switch($code) {
			case 301: $h = '301 Moved Permanently'; break;
			case 302: $h = '302 Found'; break;
			case 303: $h = '303 See Other'; break;
			default: $h = '303 See Other';
		}
		header('HTTP/1.1 '.$h);
		header('Location: '.DMS_System_Settings::getPublic('site_url').$location);
		exit();
	}
	
	/**
	 * HTTP Error redirects
	 */
	static function errorPage( $code = 404 )
	{
		header('HTTP/1.1 '.$code);
		$template = new DMS_System_Template( new DMS_System_Settings() );
		$template->load("errors/$code.php");
		$template->render();
		exit();
	}
}