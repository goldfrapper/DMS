<?php

#
# Exceptions
#
class DMS_Exceptions_DefaultException extends Exception {
// 	public function __construct($message='', $code = 0, Exception $previous = null)
// 	{
// 		var_dump();
// 		parent::__construct($message, $code, $previous);
// 	}
}
class DMS_Exceptions_InvalidProjectFile extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_NoProjectFile extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_InvalidPropertyAssignment extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_InvalidApplicationID extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_InvalidUser extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_InvalidSessionCode extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_CannotUpdateSession extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_LoggedOutSession extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_CannotLogoutUser extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_InvalidUserData extends DMS_Exceptions_DefaultException {}

class DMS_Exceptions_CannotSaveProfile extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_CannotUpdateUser extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_CannotSaveAddress extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_CannotResetUser extends DMS_Exceptions_DefaultException {}
class DMS_Exceptions_FileNotExists extends DMS_Exceptions_DefaultException {}

#
# Interfaces
#

interface DMS_Parsers_iParser
{
	public function setProjectFile( $project_file );
	public function parse();
}

interface DMS_Models_iModel
{
	
}

/**
 * Base Iterator class
 */
abstract class DMS_Objects_Iterator implements Iterator, Countable
{
	protected $position = 0;
	protected $cnt = 0;
	
	public function __construct() {}
	
	public function current() {}
	
	public function count()
	{
		return $this->cnt;
	}
	
	public function next()
	{
		$this->position++;
	}
	
	public function rewind()
	{
		$this->position = 0;
	}
	
	public function key()
	{
		return $this->position;
	}
	
	public function valid()
	{
		return (($this->position) < $this->cnt)? true : false;
	}
}




class DMS_Utils_Mailer
{
	static public function systemMail($to,$msg)
	{
		$subject = 'Message from '.$_SERVER['SERVER_NAME'];
		return DMS_Utils_Mailer::sendMail($to, $subject, $msg);
	}
	
	static public function templateMail($to,$subject,$template,$data)
	{
		if(!file_exists($template)) throw new DMS_Exceptions_FileNotExists();
		$msg = file_get_contents($template);
		foreach($data as $k=>$v) $msg = str_replace("%%$k%%",$v,$msg);
		return DMS_Utils_Mailer::sendMail($to, $subject, $msg);
	}
	
	static public function sendMail($to, $subject, $msg, $from = null)
	{
		if($from===null) $from = $_SERVER['SERVER_NAME'].' <no-reply@'.$_SERVER['SERVER_NAME'].'>';
		$headers = "From: $from\r\nReply-To: $from\r\n";
		return mail($to, $subject, $msg, $headers);
	}
}