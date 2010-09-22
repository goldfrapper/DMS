<?php
/**
 * User, Group and Session administration
 * @author Tom Van de Putte
 * @date Friday 06 August 2010
 *
 */

class DMS_User_Service
{
	public static function login()
	{
		$code = DMS_System_Service::get('code','POST');
		$login = DMS_System_Service::get('login','POST');
		$pass = DMS_System_Service::get('pass','POST');
		
		if($login===null || $pass===null) {
			DMS_System_Service::redirect('?p=login&e=empty');
		}
		
		# Try to login user
		try {
			$user = new DMS_User_User();
			$user->login($login, $pass);
		}
		catch( DMS_Exceptions_InvalidUser $e) {
			DMS_System_Service::redirect('?p=login&e=invalid');
		}
		catch( Exception $e) {
			var_dump($e); die();
		}
		
		# Start session
		try {
			// Try to revert to a previous (open) session.
			$c = DMS_User_Session::getLastOpenSessionForUser($user);
			if($c!==false) {
				
				/**
				 * FIXME: Something strange; When there is only one session for the user
				 */
				if($c!=$code) DMS_User_Session::clearSession($code);
				$code = $c;
			}
			
			$sess = DMS_User_Factory::getSession($code);
			$sess->user = $user;
			$sess->login();
		} catch( Exception $e) {
			var_dump($e); die();
		}
	}
	
	public static function logout()
	{
		$code = DMS_System_Service::get('code');
		$sess = DMS_User_Factory::getSession($code);
		try {
			$sess->user->logout();
			$sess->logout();
		} catch( DMS_Exceptions_CannotUpdateSession $e) {
			echo 'failed to log out'; return;
		}
		echo 'logged out';
	}
	
	// Update sessions mdate
	public static function updateSessionTime($code)
	{
		$sess = DMS_User_Factory::getSession($code);
		$sess->updateSessionTime();
	}
}

class DMS_User_Factory
{
	static private $sess;
	
	/**
	 * Returns session object for given session code or creates a new session
	 * then checks for send headers and sets cookie if not already set
	 */
	public static function getSession($code=null)
	{
		if(isset(self::$sess) && self::$sess instanceof DMS_User_Session) {
			return self::$sess;
		}
		
		if(!isset($code) && isset($_COOKIE['DMS_SESS'])) {
			$code = $_COOKIE['DMS_SESS'];
		} elseif(empty($code)) $code = null;
		try {
			$sess = new DMS_User_Session($code);
		} catch(DMS_Exceptions_InvalidSessionCode $e) {
			$sess = new DMS_User_Session();
		} catch( DMS_Exceptions_LoggedOutSession $e) {
			$sess = self::getSession('');
		}
		
		self::$sess = $sess;
		
		$s = false;
		foreach(headers_list() as $h) {
			if(strpos($h,'Set-Cookie')===0) $s = true;
		}
		if(!$s) setcookie('DMS_SESS', $sess->code, 0, '/');
		return $sess;
	}
}


class DMS_User_Session implements DMS_Models_iModel
{
	public $id;
	public $user;
	public $code;
	public $cdate;
	public $mdate;
	public $status;
	
	public function __construct($code=null)
	{
		$pdo = new DMS_DB_PDO();
		if(is_null($code)) {
			$this->code = md5(uniqid('dms_'));
			$r = $pdo->exec("INSERT INTO dms_sessions (code,mdate) VALUES('{$this->code}',NOW());");
			if(!$r) {
				throw new Exception();
			}
		} else $this->code = $code;
		$stat = $pdo->query("SELECT * FROM dms_sessions WHERE code='{$this->code}';");
		if(!$stat instanceOf PDOStatement) {
			throw new Exception();
		}
		$all = $stat->fetchAll(PDO::FETCH_ASSOC);
		
		if(!sizeof($all)) {
			throw new DMS_Exceptions_InvalidSessionCode();
		}
		$this->id = $all[0]['id'];
		$this->cdate = $all[0]['cdate'];
		$this->mdate = $all[0]['mdate'];
		$this->status = $all[0]['status'];
		if($this->status==0) {
			throw new DMS_Exceptions_LoggedOutSession();
		}
		$user_id = $all[0]['user_id'];
		if(is_numeric($user_id)) $this->user = new DMS_User_User($user_id);
	}
	
	public function login()
	{
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec("UPDATE dms_sessions SET user_id={$this->user->id} WHERE id={$this->id};");
		if($r===false) throw new DMS_Exceptions_CannotUpdateSession();
	}
	
	public function logout()
	{
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec("UPDATE dms_sessions SET status=0 WHERE id={$this->id};");
		if($r===false) throw new DMS_Exceptions_CannotUpdateSession();
		return true;
	}
	
	public function updateSessionTime()
	{
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec("UPDATE dms_sessions SET mdate=NOW() WHERE id={$this->id};");
		if($r===false) throw new DMS_Exceptions_CannotUpdateSession();
	}
	
	public static function getLastOpenSessionForUser(DMS_User_User $user)
	{
		$pdo = new DMS_DB_PDO();
		$sql = "SELECT code FROM dms_sessions WHERE user_id={$user->id} ORDER BY mdate DESC LIMIT 1;";
		$stat = $pdo->query($sql);
		if(!$stat instanceOf PDOStatement) throw new Exception();
		$all = $stat->fetchAll(PDO::FETCH_ASSOC);
		if(!sizeof($all)) return false;
		return $all[0]['code'];
	}
	
	public static function clearSession($code)
	{
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec("DELETE FROM dms_sessions WHERE code='$code';");
		if($r===false) throw new DMS_Exceptions_CannotUpdateSession();
	}
}

class DMS_User_User implements DMS_Models_iModel
{
	const EMPTY_VALUES = -1;
	const BAD_USERNAME = -2;
	const BAD_EMAIL = -4;
	const USER_EXISTS = -8;
	const EMAIL_EXISTS = -16;
	const USER_ANOMYMOUS = -32;
	
	public $id = 0;
	public $name = 'anonymous';
	public $fname = 'Anonymous';
	public $email = 'anonymous@agx.eu';
	
	private $status = 0;
	private $groups = array();
	
	public function __construct($user_id=null)
	{
		if(is_numeric($user_id) & $user_id!=0) {
			$pdo = new DMS_DB_PDO();
			$stat = $pdo->query("SELECT * FROM dms_users WHERE id=$user_id;");
			$this->_setDataFromDB($stat);
		}
	}
	
	public function login($login, $pass)
	{
		$pdo = new DMS_DB_PDO();
		
		$email_login = DMS_System_Settings::getPublic('user_email_login');
		
		$col = ($email_login)? 'email':'name';
		$stat = $pdo->query("SELECT * FROM dms_users WHERE $col='$login' AND pass=SHA1('$pass');");
		$this->_setDataFromDB($stat);
		
		$r = $pdo->exec("UPDATE dms_users SET status=1 WHERE id={$this->id};");
		if($r===false) throw new Exception();
		$this->status = 1;
		return true;
	}
	
	public function validate()
	{
		# Empty values
		if( empty($this->name) && empty($this->email)) {
			return DMS_User_User::EMPTY_VALUES;
		}
		
		# If user is still anonymous
		if(!$this->id && $this->name=='anonymous') return self::USER_ANOMYMOUS;
		
		$o = 0;
		
		# Malformatted username?
		if(strlen($this->name>20) || !preg_match('/^[a-zA-Z0-9]+$/',$this->name)) {
			$o = self::BAD_USERNAME;
		}
		
		# Email is not an email address
		if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$this->email)) {
			$o += DMS_User_User::BAD_EMAIL;
		}
		if($o!=0) return $o;
		
		if(!$this->id) {
			
			# Does username exists?
			$pdo = new DMS_DB_PDO();
			$stat = $pdo->query("SELECT COUNT(*) FROM dms_users WHERE name='{$this->name}'");
			$all = $stat->fetchAll(PDO::FETCH_NUM);
			if($all===false || empty($all) || $all[0][0]!=0) $o = DMS_User_User::USER_EXISTS;
			
			# Does email address exists?
			$stat = $pdo->query("SELECT COUNT(*) FROM dms_users WHERE email='{$this->email}'");
			$all = $stat->fetchAll(PDO::FETCH_NUM);
			if($all===false || empty($all) || $all[0][0]!=0) $o += DMS_User_User::EMAIL_EXISTS;
		}
		if($o!=0) return $o;
		
		return true;
	}
	
	public function save( $pass = null )
	{
		if(!$this->validate()) throw new DMS_Exceptions_InvalidUserData();
		
		if($this->id!=0) {
			$sql = "UPDATE dms_users SET name='{$this->name}',fname='{$this->fname}',email='{$this->email}'";
			if($pass!==null) $sql.= ",pass=SHA1('$pass')";
			$sql.= " WHERE id={$this->id}";
		} else {
			if($pass!==null) {
				$sql = 'INSERT INTO dms_users (name,pass,fname,email) VALUES (';
				$sql.= "'{$this->name}',SHA1('$pass'),'{$this->fname}','{$this->email}') ";
			} else {
				$sql = 'INSERT INTO dms_users (name,fname,email) VALUES (';
				$sql.= "'{$this->name}','{$this->fname}','{$this->email}')";
			}
		}
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec($sql);
		if($r===false) throw new DMS_Exceptions_CannotUpdateUser();
		if(!$this->id) $this->id = $pdo->lastInsertId();
	}
	
	public function resetPass( $name = null, $email = null, $new_pass )
	{
		if($name===null && $email===null) return;
		
		$sql = "SELECT * FROM dms_users WHERE ";
		$sql .= ($name!==null && !empty($name))? "name='$name'" : "email='$email'";
		$pdo = new DMS_DB_PDO();
		$stat = $pdo->query($sql);
		try {
			$this->_setDataFromDB($stat);
		} catch(Exception $e) {
			throw new DMS_Exceptions_CannotResetUser();
		}
		
		/**
		 * BUG: The status of the user should be altered and a reset password request mail
		 * should be send. Only 1 mail should be send while status is unchanged.
		 */
		
		$r = $pdo->exec("UPDATE dms_users SET pass=SHA1('$new_pass') WHERE id={$this->id}");
		if($r===false) throw new DMS_Exceptions_CannotResetUser();
		return true;
	}
	
	public function logout()
	{
		if(!$id) return;
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec("UPDATE dms_users SET status=0 WHERE id={$this->id};");
		if($r===false) throw new DMS_Exceptions_CannotLogoutUser();
		$this->status = 0;
		return true;
	}
	
	public function getGroupIterator()
	{
		$pdo = new DMS_DB_PDO();
		$stat = $pdo->query("SELECT group_id AS id FROM dms_usergroups WHERE user_id={$this->id};");
		return new DMS_User_GroupIterator($stat);
	}
	
	private function _setDataFromDB( PDOStatement $stat )
	{
		if($stat===false) return false;
		if($stat->rowCount()==0) {
			throw new DMS_Exceptions_InvalidUser();
		}
		$all = $stat->fetchAll(PDO::FETCH_ASSOC);
		if(!sizeof($all)) {
			throw new Exception();
		}
		$this->id = $all[0]['id'];
		$this->name = $all[0]['name'];
		$this->fname = $all[0]['fname'];
		$this->email = $all[0]['email'];
		$this->status = $all[0]['status'];
	}
}

class DMS_User_Group implements DMS_Models_iModel
{
	public $id;
	public $name;
	
	private $appLinks = array();
	
	public function __construct($id)
	{
		if(!is_numeric($id)) {
			throw new Exception();
		}
		$this->id=$id;
		$pdo = new DMS_DB_PDO();
		$st = $pdo->query("SELECT appID FROM dms_groupperms WHERE group_id=$id;");
		while(($c = $st->fetch(PDO::FETCH_NUM))!==false) {
			$this->appLinks[] = $c[0];
		}
	}
	
	public function getName()
	{
		if(empty($this->name)) {
			$pdo = new DMS_DB_PDO();
			$st = $pdo->query("SELECT name FROM dms_groups WHERE id={$this->id};");
			if(($c = $st->fetchAll(PDO::FETCH_NUM))!==false && !empty($c[0])) {
				$this->name = $c[0][0];
			}
		}
		return $this->name;
	}
	
	public function hasPermission(DMS_Models_AppLink $appLink)
	{
		if(in_array($appLink->appID,$this->appLinks)) return true;
		return false;
	}
}

class DMS_User_GroupIterator extends DMS_DB_PDOIterator
{
	public function current()
	{
		$c = parent::current();
		return new DMS_User_Group($c['id']);
	}
}