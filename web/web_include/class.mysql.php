<?php
Class Class_Mysql{
	public $link;
	public $query;
	function __construct($host,$user_name,$password,$db_name){
		$this->link = mysql_connect($host,$user_name,$password) or die("Cound not connect to $host");
		mysql_select_db($db_name,$this->link) or die("Cound not select db $db_name");
		mysql_query("set names utf8");
	}
	function query($sql){  
		$this -> query = mysql_query($sql);
		return $this -> query;
	}
	function fetch_array($query){
		return mysql_fetch_array($query );
	}
	function getone($sql,$array_type=MYSQL_BOTH){
		$this->query = $this->query($sql);
		return  @mysql_fetch_array($this->query,$array_type);
	}
	function getall($sql){
		$this->query = $this->query($sql);
		while ($rows = @mysql_fetch_array($this->query)){
			$array[] = $rows;
		}
		return $array;
	}
	function getoptions($sql){
		$this->query = $this->query($sql);
		while ($rows = mysql_fetch_array($this->query)){
			$array[$rows[1]] = $rows[0];
		}
		return $array;
	}
	function num_rows($sql){
		return @mysql_num_rows($this->query($sql));
	}
	function insert_id(){
		return mysql_insert_id();
	}
}
			
$db_host      =  $web_config['Web_DB_Host'];
$db_username  =  $web_config['Web_DB_User'];
$db_password  =  $web_config['Web_DB_Pass'];
$db_name      =  $web_config['Web_DB_Name'];
$db_pre       =  "db_";
$db = new Class_Mysql($db_host,$db_username,$db_password,$db_name);
?>