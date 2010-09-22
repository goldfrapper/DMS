<?php
/**
 * DMS - Settings module
 */

/**
 * DMS Configuration system
 *
 * Based on a sollution given in a stackoverflow thread (3724584).
 * @see http://stackoverflow.com/questions/3724584/what-is-the-best-way-to-save-config-variables-in-a-php-web-app
 */
class DMS_System_Settings
{
	static private $public = array();
	static private $protected = array();
	
	
	/**
	 * Static methods
	 */
	
	public static function getProtected( $param )
	{
		return isset(self::$protected[$param])? self::$protected[$param] : null;
	}
	
	public static function getPublic( $param )
	{
		return isset(self::$public[$param])? self::$public[$param] : null;
	}
	
	public static function setProtected( $param, $value )
	{
		self::$protected[$param] = $value;
	}
	
	public static function setPublic( $param, $value )
	{
		self::$public[$param] = $value;
	}
	
	/**
	 * Object methods
	 */
	
	public function __get( $value )
	{
		return isset(self::$public[$value])? self::$public[$value] : null;
	}
	
	public function __isset( $value )
	{
		return isset(self::$public[$value]);
	}
}