<?php
/****************************************
docs.paxagency.com/php/libraries/db
*****************************************/
class db {
	public $connection;
	public $string='';
	public $values = [];
	private $displayErrors = true;
 	public function __construct() {
        try {
			$this->connection = new \PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		} catch(PDOException $exception ) {
			return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
    }
	/**
	* Create Connection
	* @param  string 	$server
	* @param  string	$name
	* @param  string  	$user
	* @return string	$pass
	*/
	public function connect($server,$name,$user,$pass){
		$this->connection = new \PDO('mysql:host='.$server.';dbname='.$name.';charset=utf8mb4', $user, $pass);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	/**
	* Get One Row
	* @param  string 		 $table
	* @param  array|integer  $data
	* @param  array  		 $select
	* @return array
	*/
    public function get($table,$data,$select=[]){
		$vals = (!$select) ? '*' : implode(',',$select);
		$string = "SELECT ".$vals." FROM ".$table;
	 	$values = [];
		if(is_array($data)){
			$second = false;
			foreach($data as $key=>$var) {
    			$string.= ($second) ? " AND ".$key."=?" : " WHERE ".$key."=?";
				$values[]=$var;
				$second = true;
    	 	}
		} else {
			$string.= " WHERE id=?";
			$values[]=$data;
		}
		
		try {
			$query = $this->connection->prepare($string);
			$query->execute($values);
			$results = $query->fetchAll(PDO::FETCH_ASSOC);
			return ($results) ? $results[0] : $results;
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
  	}
  	
	/**
	* Save New Row
	* @param  string $table
	* @param  array  $data 
	* @return integer
	*/
  	public function save($table,$data){
		$values = [];
		$keys = $vars = '';
		if($this->assoc($data)){
			$second = false;
			$vars = '(';
			foreach($data as $k=>$v) {
				if($second) {$keys.=","; $vars.=",";}
				$keys.=$k;
				$vars.=':'.$k;
				$values[':'.$k]=$v;
				$second = true;
			}
			$vars.=")";
		} else {
			$bulk = $this->formatBulk($data);
			$keys = implode($bulk['keys'],',');
			foreach($bulk['data'] as $n=>$d) {
				if($n) $vars.=",";
				$vars .= '(';
				foreach($d as $i=>$v) {
					$k = $bulk['keys'][$i];
					if($i) $vars.=",";
					$vars.=':'.$k.$n;
					$values[':'.$k.$n]=$v;
				}
				$vars.=")";
			}
		}
		$string= "INSERT INTO ".$table." (".$keys.") VALUES  ".$vars;
		try {
			$query = $this->connection->prepare($string);
			$query->execute($values);
			return $this->connection->lastInsertId();
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
  	}
	public function formatBulk($data){
		$headers = $results = [];
		foreach($data as $obj){
			foreach($obj as $k=>$b) if(!in_array($k,$headers)) $headers[] = $k;
		}
		foreach($data as $n=>$obj){
			foreach($headers as $i=>$o) $results[$n][] = (isset($obj[$o])) ? $obj[$o] : null;
		}
		return ['keys'=>$headers,'data'=>$results];
	}
	/**
	* Update Row
	* @param  string   $table
	* @param  integer  $id
	* @param  array    $data
	* @return integer
	*/
	public function update($table,$id,$data){
		$q = "UPDATE ".$table." SET ";
		$second = false;
		$values = [];
		foreach($data as $key=>$var) {
			if($second) $q.=",";
			$q.= $key."=?";
			$values[]=$var;
			$second = true;
		}
		$q.= " WHERE id=?";
		$values[]=$id;
		
		try {
			$query = $this->connection->prepare($q);
			$query->execute($values);
			return $query->rowCount();
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
  	}

	/**
	* Count Table
	* @param  string   $table
	* @param  array    $query
	* @return integer
	*/
	public function count($table,$query=[]){
		$string = '';
		$values = [];
		if($query){
			$build = $this->buildWhere($query);
		 	$string = $build['query'];
			$values = $build['values'];
		}
		$string = "SELECT COUNT(*) FROM ".$table.$string;
		
		try {
			$query = $this->connection->prepare($string);
			$query->execute($values);
			return $query->fetchColumn();
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
	}

	/**
	* Remove Row
	* @param  string   $table
	* @param  integer    $id
	* @return integer
	*/
	public function delete($table,$id) {
		$string = "DELETE FROM ".$table." WHERE id=?";
		$values[] = $id;
		try {
			$query = $this->connection->prepare($string);
			$query->execute($values);
			return $query->rowCount();
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
	}
	/**
	* Search Row
	* @param  string   $table
	* @param  array    $query
	* @param  integer  $max
	* @param  integer  $page
	* @param  string   $sort
	* @param  string   $order
	* @param  integer  $count
	* @return integer
	*/
	public function search($table,$query=[],$max=10,$page=0,$sort='ASC',$order='id',$count=1) {
		$build = $this->buildWhere($query);
		$fields = (isset($query['select'])) ? $query['select'] : [];
		$string = $build['query'];
		$values = $build['values'];
		$join = $this->buildJoin($query,$fields);
		
		$query_count = ($count) ? $this->countString("SELECT Count(1) FROM ".$table.$join['string'].$string,$values) : null;
		if(!$max) ['count'=>$query_count,'hits'=>[]];
		$start = $page * $max;
		$order = (isset($query['join']) && strpos($order, '.')==false) ? $table.'.'.$order : $order;
		$group = (isset($query['group'])) ? " GROUP BY ".$query['group'] : "";
		$string.=$group;
		$string.=" ORDER BY ".$order." ".$sort;
		$string.=" LIMIT ".$start.",".$max;
		$string = "SELECT ".$join['fields']." FROM ".$table.$join['string'].$string;
	
		try {
			if($query){
				$call = $this->connection->prepare($string);
				$call->execute($values);
				$results= $call->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$call = $this->connection->query($string);
				$results = $call->fetchAll(PDO::FETCH_ASSOC);
			}
			return ['count'=>$query_count,'hits'=>$results];
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
	}

	/**
	* Get One Row
	* @param  string $table
	* @param  array  $query
	* @return array
	*/
	public function searchGet($table,$query) {
		$select = (isset($query['select'])) ? $query['select'] : [];
		$build = $this->buildWhere($query);
		$join = $this->buildJoin($query,$select);
		$string = "SELECT ".$join['fields']." FROM ".$table.$join['string'].$build['query'];
		
		try {
			$call = $this->connection->prepare($string);
			$call->execute($build['values']);
			$results= $call->fetchAll(PDO::FETCH_ASSOC);
			return ($results) ? $results[0] : $results;
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
	}

	/**
	* Update Row
	* @param  string   $table
	* @param  array    $query
	* @param  array    $data
	* @return integer
	*/
  	public function searchUpdate($table,$query,$data){
		$build = $this->buildWhere($query);
		$string = "UPDATE ".$table." SET ";
		$second = false;
		$values = [];
		foreach($data as $key=>$var) {
			if($second) $string.=",";
			$string.= $key."=?";
			$values[]=$var;
			$second = true;
		}
		$string.=$build['query'];
		$values=$build['values'];
		try {
			$query=$this->connection->prepare($string);
			$query->execute($values);
			return $query->rowCount();
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
  	}

	/**
	* Query From Raw String
	* @param  string $string
	* @return mixed
	*/
	public function query($string){
		try {
			$query = $this->connection->query($string);
			return $query->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
  	}

	/**
	* Count Table By String
	* @param  string   $string
	* @param  array    $values
	* @return integer
	*/
	public function countString($string,$values){
		try {
			$query = $this->connection->prepare($string);
			$query->execute($values);
			return $query->fetchColumn();
		} catch (PDOException $e) {
		    return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
		}
	}
	public function getType($p){
		$v = [
			'int'=>['type'=>' int(10) ','null'=>' NOT NULL ','default'=>''],
			'key'=>['type'=>' int(10) ','null'=>' unsigned NOT NULL AUTO_INCREMENT','default'=>''],
			'tinyint'=>['type'=>' tinyint(4) ','null'=>' NOT NULL','default'=>'0'],
			'string'=>['type'=>' varchar(191)','null'=>' COLLATE utf8mb4_unicode_ci NOT NULL ','default'=>''],
			'date'=>['type'=>' timestamp ','null'=>' NULL ','default'=>'NULL'],
			'datetime'=>['type'=>' timestamp','null'=>' NULL ','default'=>'NULL'],
			'medium'=>['type'=>' mediumtext','null'=>' NULL ','default'=>''],
		];
		$o = $v[$p['type']];
		$str=$o['type'];
		$str.=(isset($p['null'])) ? $p['null'] : $o['null'];
		$default = (isset($p['default'])) ? (string)$p['default'] : $o['default'];
		if($default!='') $default = ' DEFAULT '.$default;
		return $str.=$default;
	}
	public function setup($map){
		$query = $this->connection->query("DROP DATABASE ".DB_NAME.";");
		$query = $this->connection->query("CREATE DATABASE ".DB_NAME.";");
		$this->__construct();
		$sql='';
		foreach($map['tables'] as $table=>$rows){
			$sql="CREATE TABLE IF NOT EXISTS {$table} (";
			foreach($rows as $n=>$r) {
				if($n) $sql.=', ';
				$sql.='`'.$r.'` '.$this->getType($map['fields'][$r]);
			}
			$sql.=", PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; ";
			try {
				return $this->connection->query($sql);
			} catch (PDOException $e) {
				return $this->displayErrors ? ["error"=>$e->getMessage()] : [];
			}
		}
	}
	public function buildJoin($query,$fields){
		if(!isset($query['join'])) {
			$vals = (!$fields) ? '*' : implode(',',$fields);
			return ['string'=>'','fields'=>$vals];
		} else {
			$vals = '';
			$string = ' ';
			if(!$fields) {
				$vals='*';
			} else {
				foreach($fields as $n=>$f) {
					if($n) $vals.=',';
					if(strpos($f, '.')==false) {
						$vals.=$f;
					} else {
						$vals.=$f.' AS ';
						$alias = strtolower(str_replace(['.','(',')'],['_','_',''],$f));
						$vals.=$alias;
					}
				}
			}
			foreach($query['join'] as $n=>$j) {
				$string.=strtoupper($j[0]).' '.$j[1].' ON '.$j[2].'='.$j[3].' ';
			}
			return ['string'=>$string,'fields'=>$vals];
		}
	}
	public function assoc($array){
        return ($array !== array_values($array));
    }
	public function buildWhere($data){
		if(!isset($data['and']) && !isset($data['or'])) return ['query'=>'','values'=>[]];
        $this->values = [];
		if(isset($data['and'])) $str=$this->_if($data['and'],'AND');
        if(isset($data['or'])) $str=$this->_if($data['or'],'OR');
		$str = ' WHERE '.$str.' ';
        return ['query'=>$str,'values'=>$this->values];
    }
	public function _if($array,$type='AND'){
        $str='';
        if($this->assoc($array[0])){
            foreach($array as $n=>$if) {
                $key = key($if);
                if($n) $str.=' '.strtoupper($type).' ';
                $str.='('.$this->_if($if[$key],$key).')';
            }
        } else {
            $str.=$this->ifBlock($array,$type);
        }
        return $str;
    }
    public function ifBlock($array,$type='AND'){
        $str='';
       	foreach($array as $n=>$m){
           $key = $m[0];
           $action = strtoupper($m[1]);
           $val = $this->ifValue($m[2],$action);
           if($n) $str.=' '.strtoupper($type).' ';
           $str.= $key." ".$action." ".$val;
       }
       return $str;
   }
   public function ifValue($v,$a){
       $val = '';
       if($a=='IN' || $a=='GREATEST' || $a=='NOT IN'){
           foreach($v as $n=>$o){
               $val.=($n) ? ',?' : '(?';
               $this->values[]=$o;
           }
           $val.=')';
       } elseif($a=='BETWEEN'){
           $this->values[]=$v[0];
           $this->values[]=$v[1];
           $val='? AND ?';
       } else {
           $this->values[]=$v;
           $val='?';
       }
       return $val;
   }
}

?>
