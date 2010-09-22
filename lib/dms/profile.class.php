<?php
/**
 * User profile and messaging library
 * @author Tom Van de Putte
 * @copyright AGX 2010
 */






class DMS_User_ProfileService
{
	
	/**
	 * Create or edit a profile
	 * Also creates the user if it not already exists
	 * @param Array $post_data Array containing POST data
	 */
	static function saveProfile( Array $post_data)
	{
		
	}
}


/**
 * Extends the base User concept
 */
class DMS_User_Profile implements DMS_Models_iModel
{
	protected $user;
	protected $data;
	protected $addressbook;
	
	public function __construct( DMS_User_User $user )
	{
		$this->user = $user;
		$this->data = new DMS_User_ProfileData();
		
		$this->data->fn = $this->user->fname;
		$this->data->email = $this->user->email;
		
		$this->_setDataFromDB();
		$this->_loadAddressBook();
	}
	
	private function _setDataFromDB()
	{
		$pdo = new DMS_DB_PDO();
		$stat = $pdo->query("SELECT * FROM dms_profiles WHERE user_id={$this->user->id};");
		if($stat===false) return false;
		$all = $stat->fetchAll(PDO::FETCH_ASSOC);
		if(empty($all)) return;
		$this->name = $all[0]['name'];
		$this->bday = $all[0]['bday'];
		$this->sex = $all[0]['sex'];
		$this->lng = $all[0]['lng'];
		$this->title = $all[0]['title'];
	}
	
	private function _loadAddressBook()
	{
		$pdo = new DMS_DB_PDO();
		$sql = "SELECT * FROM dms_addressbook a INNER JOIN dms_useraddresses b USING(address_id)
			WHERE b.user_id={$this->user->id}";
		$stat = $pdo->query($sql);
		$iter = new DMS_User_AddressIterator($stat);
		foreach($iter as $address) $this->addressbook[$address->addressname] = $address;
	}
	
	public function getUserId()
	{
		return $this->user->id;
	}
	
	public function __get($name)
	{
		if(!isset($this->data->$name)) return null;
		return $this->data->$name;
	}
	
	public function __set($name, $value)
	{
		$this->data->$name = $value;
	}
	
	public function __isset($name)
	{
		return isset($this->data->$name);
	}
	
	public function getAddress( $name )
	{
		if(!isset($this->addressbook[$name])) return false;
		return $this->addressbook[$name];
	}
	
	public function setAddress( $name, DMS_User_Address $address )
	{
		$address->addressname = $name;
		$this->addressbook[$name] = $address;
	}
	
	public function save()
	{
		$data = array(
			'name' => "'{$this->data->name}'",
			'bday' => "'{$this->data->bday}'",
			'sex' => "'{$this->data->sex}'",
			'lng' => "'{$this->data->lng}'",
			'title' => "'{$this->data->title}'"
		);
		$sql = 'INSERT INTO dms_profiles VALUES('.$this->user->id.','.implode(',',$data);
		$n = array();
		foreach($data as $k=>$v) $n[] = "$k=$v";
		$sql.= ') ON DUPLICATE KEY UPDATE '.implode(',',$n);
		
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec($sql);
		if($r===false) throw new DMS_Exceptions_CannotSaveProfile();
		
		# Check addresses already in system
		$addresses = array();
		$sql = "SELECT addressname, address_id FROM dms_useraddresses
			WHERE user_id={$this->user->id}";
		$stat = $pdo->query($sql);
		while(($c=$stat->fetch(PDO::FETCH_ASSOC))!==false) {
			$addresses[$c['addressname']] = $c['address_id'];
		}
		
		foreach($this->addressbook as $address) {
			
			if(array_key_exists($address->addressname,$addresses)) {
				$address->address_id = $addresses[$address->addressname];
				$address->save();
			} else {
				$address->save();
				$sql = 'INSERT INTO dms_useraddresses VALUES(';
				$sql.= "{$this->user->id},{$address->address_id},'{$address->addressname}') ";
				$sql.= "ON DUPLICATE KEY UPDATE addressname='{$address->addressname}'";
				$r = $pdo->exec($sql);
				if($r===false) throw new DMS_Exceptions_CannotSaveAddress();
			}
		}
		
		return true;
	}
}


class DMS_User_ProfileIterator extends DMS_DB_PDOIterator
{
	public function __construct( $where )
	{
		$sql = "SELECT DISTINCT dms_profiles.user_id FROM dms_profiles
			INNER JOIN dms_useraddresses USING(user_id)
			INNER JOIN dms_addressbook USING(address_id) WHERE $where";
		$pdo = new DMS_DB_PDO();
		
		$stat = $pdo->query($sql);
		parent::__construct($stat);
	}
	
	public function current()
	{
		$c = parent::current();
		$user = new DMS_User_User($c['user_id']);
		return new DMS_User_Profile($user);
	}
}

class DMS_User_AddressIterator extends DMS_DB_PDOIterator
{
	public function current()
	{
		$c = parent::current();
		return new DMS_User_Address($c);
	}
}

class DMS_User_Address implements DMS_Models_iModel
{
	public $address_id = 0;
	public $addressname;
	public $name;
	public $street;
	public $nr;
	public $postalcode;
	public $city;
	public $county;
	public $country;
	
	public function __construct($data = null)
	{
		if($data===null) return;
		foreach($data as $k=>$v) $this->$k = $v;
	}
	
	public function validate()
	{
		if(
			!empty($this->name) &&
			!empty($this->city)
		) return true;
		
		return false;
	}
	
	public function save()
	{
		$fields = array('name','street','nr','postalcode','city','county','country');
		$v1 = $v2 = array();
		foreach($fields as $f) {
			$v1[] = "$f='".$this->$f."'";
			$v2[] = "'".$this->$f."'";
		}
		if($this->address_id!=0) {
			$sql = 'UPDATE dms_addressbook SET '.implode(',',$v1).' WHERE address_id='.$this->address_id;
		} else {
			$sql = 'INSERT INTO dms_addressbook ('.implode(',',$fields).') VALUES('.implode(',',$v2).')';
		}
		$pdo = new DMS_DB_PDO();
		$r = $pdo->exec($sql);
		if($r===false) throw new DMS_Exceptions_CannotSaveAddress();
		$this->address_id = $pdo->lastInsertId();
		return true;
	}
}

class DMS_User_ProfileData implements DMS_Models_iModel
{
	/**
	 * Name; Formatted as lastname; firstname
	 */
	public $name;
	
	/**
	 * Formatted name, Screen name
	 */
	public $fn;
	
	public $bday;
	
	public $email;
	
	public $sex;
	
	public $lng;
	
	public $title;
}

