<?php

/**
 * CMTA PDO wrappers
 */

/**
 * PDO Wrapper; connects automaticly to MySQL DB
 *
 * TODO: Throw PDO errors as Exceptions from overloaded methods.
 */
class DMS_DB_PDO extends PDO
{
	static private $instance;
	
	public static function getInstance()
	{
		if(!(self::$instance instanceof DMS_DB_PDO)) self::$instance = new DMS_DB_PDO();
		return self::$instance;
	}
	
	public function __construct()
	{
		$host = DMS_System_Settings::getProtected('pdo_host');
		$db = DMS_System_Settings::getProtected('pdo_database');
		$user = DMS_System_Settings::getProtected('pdo_user');
		$pass = DMS_System_Settings::getProtected('pdo_pass');
		
		$driverOptions[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
		parent::__construct("mysql:dbname=$db;host=$host",$user,$pass,$driverOptions);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DMS_DB_PDOStatement', array($this)));
	}
	
	public function exec($sql)
	{
		$this->_log($sql);
		return parent::exec($sql);
	}
	
	public function query($sql)
	{
		$this->_log($sql);
		return parent::query($sql);
	}
	
	private function _log($sql)
	{
// 		var_dump($sql);
	}
}

class DMS_DB_PDOStatement extends PDOStatement
{
// 	public $db;
// 	public $foundRows;
	
	protected function __construct($db)
	{
// 		$this->db = $db;
	}
	
// 	public function execute( $array = null )
// 	{
// 	var_dump('dfgdfgfd');
// 		if($array===null) $result = parent::execute();
// 		else parent::execute($array);
// 		$this->foundRows = $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
// 		return $result;
// 	}

// 	public function rowCount()
// 	{
// 		return $this->foundRows;
// 	}
}

class DMS_DB_PDOIterator implements Iterator, Countable
{
	protected $statement;
	protected $position = 0;
	protected $data = array();
	protected $cnt;
	
	public function __construct(PDOStatement $stat)
	{
		$this->statement = $stat;
		$this->cnt = $this->statement->rowCount();
// 		$pdo = new DMS_DB_PDO();
// 		$stat = $pdo->query('SELECT FOUND_ROWS()');
// 		$c = $stat->fetchAll();
// 		var_dump($c);
	}
	
	public function current()
	{
// 		var_dump('current ('.$this->position.')');
		if(!isset($this->data[$this->position])) {
			$c = $this->statement->fetch(PDO::FETCH_ASSOC,PDO::FETCH_ORI_NEXT,$this->position);
			$this->data[$this->position]=$c;
		} else $c = $this->data[$this->position];
		return $c;
	}
	
	public function count()
	{
		return $this->cnt;
	}
	
	public function next()
	{
// 		var_dump('next');
		$this->position++;
	}
	
	public function rewind()
	{
// 		var_dump('rewind');
		$this->position = 0;
	}
	
	public function key()
	{
// 		var_dump('key');
		return $this->position;
	}
	
	public function valid()
	{
// 		var_dump('valid ('.(($this->position) < $this->cnt).')');
		return (($this->position) < $this->cnt)? true : false;
	}
}

// $pdo = new DMS_DB_PDO();
// $sql = "SELECT * FROM dms_addressbook LIMIT 1";
// $stat = $pdo->query($sql);
// 
// var_dump($stat->rowCount());
// 
// $iter = new DMS_DB_PDOIterator($stat);
// foreach($iter as $address) var_dump($address['name']);