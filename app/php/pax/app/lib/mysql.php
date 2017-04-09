<?php
/***************************************
****************************************
8888888b.     d8888Y88b   d88P
888   Y88b   d88888 Y88b d88P
888    888  d88P888  Y88o88P
888   d88P d88P 888   Y888P
8888888P" d88P  888   d888b
888      d88P   888  d88888b
888     d8888888888 d88P Y88b
888    d88P     888d88P   Y88b

MYSQL V 1.0
Copyright 2016 PAXagency
Created by Albert Kiteck
www.paxagency.com
****************************************
****************************************/

class mysql {
	public $connection;
 	 public function __construct() {
        $this->connection = new \PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
    public function get($table, $data=false,$order=null,$sort='ASC', $page=0, $max=100){
    		$q = "SELECT * FROM ".$table;
    		$second = false;
		 	  $values = [];
    		foreach($data as $key=>$var) {
    			    $q.= ($second) ? " AND ".$key."=?" : " WHERE ".$key."=?";
    	 	 	    $second = true;
							$values[]=$var;
    	 	}
    		if($order) $q.=" ORDER BY ".$order." ".$sort;
      	$start = $page * $max;
      	$q.=" LIMIT ".$start.",".$max;

				if($data) {
						$query = $this->connection->prepare($q);
						$query->execute($values);
						return $query->fetchAll(PDO::FETCH_ASSOC);
				} else {
						$query = $this->connection->query($q);
						return $query->fetchAll(PDO::FETCH_ASSOC);
				}
  	}
    public function query($q){
				$query = $this->connection->query($q);
				$results = $query->fetchAll(PDO::FETCH_ASSOC);
				return  $results;
  	}
  	public function save($t, $k){
    		$second = false;
			$values = [];
    		foreach($k as $k=>$v)  {
    			if($second) $keys.=",";
    			if($second) $vars.=",";
    			$keys.=$k;
    			$vars.=':'.$k;
					$values[':'.$k]=$v;
    	 	 	$second = true;
    		}
    		$q= "INSERT INTO ".$t." (".$keys.") VALUES ( ".$vars.")";

				$query = $this->connection->prepare($q);
				$query->execute($values);
				return $this->connection->lastInsertId();
  	}
  	public function update($table,$data,$where){
    		$q = "UPDATE ".$table." SET ";
    		$second = false;
				$values = [];
    		foreach($data as $key=>$var) {
    			if($second) $q.=",";
    			$q.= $key."=?";
					$values[]=$var;
    			$second = true;
    		}
        $second = false;
    		foreach($where as $key=>$var) {
    			    $q.= ($second) ? " AND ".$key."=?" : " WHERE ".$key."=?";
							$values[]=$var;
    	 	 	    $second = true;
    	 	}
				$query = $this->connection->prepare($q);
				$query->execute($values);
				return $query->rowCount();
  	}
    public function count($table, $data){
    		$q = "SELECT COUNT(*) FROM ".$table;
    		$second = false;
				$values = [];
				foreach($data as $key=>$var) {
    			    $q.= ($second) ? " AND ".$key."=?" : " WHERE ".$key."=?";
							$values[]=$var;
    	 	 	    $second = true;
    	 	}
				$query = $this->connection->prepare($q);
				$query->execute($values);
				return $query->fetchColumn();
  	}
	public function delete($table,$data) {
		  	$q = "DELETE FROM ".$table;
				$second = false;
				$values = [];
    		foreach($data as $key=>$var) {
    			    $q.= ($second) ? " AND ".$key."=?" : " WHERE ".$key."=?";
    	 	 	    $second = true;
							$values[]=$var;
    	 	}
				$query = $this->connection->prepare($q);
				$query->execute($values);
				return $query->rowCount();
	}
	public function search($table,$keys,$search) {
				$q = "SELECT * FROM ".$table;
				$second = false;
				$values = [];
				foreach($keys as $key) {
							$q.= ($second) ? " OR ".$key." LIKE ?" : " WHERE ".$key." LIKE ?";
							$second = true;
							$values[]='%'.$search.'%';
				}
				$query = $this->connection->prepare($q);
				$query->execute($values);
				return $query->fetchAll(PDO::FETCH_ASSOC);
	}
}



?>
