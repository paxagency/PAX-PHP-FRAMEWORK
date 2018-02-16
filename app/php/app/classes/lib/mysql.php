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
Copyright 2018 PAXagency
Created by Albert Kiteck
www.paxagency.com
****************************************
****************************************/

class mysql {
	public $connection;
	public $string='';
	public $values = [];
 	public function __construct() {
        $this->connection = new \PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
	public function query($string){
		$query = $this->connection->query($string);
		$results = $query->fetchAll(PDO::FETCH_ASSOC);
		return  $results;
  	}
    public function get($table,$data,$fields=[]){
		$vals = (!$fields) ? '*' : implode(',',$fields);
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
		$query = $this->connection->prepare($string);
		$query->execute($values);
		$results = $query->fetchAll(PDO::FETCH_ASSOC);
		return ($results) ? $results[0] : $results;
  	}
	public function getSearch($table,$query=[],$fields=[]) {
		$build = $this->buildWhere($query);
		$join = $this->buildJoin($query,$fields);
		$string = "SELECT ".$join['fields']." FROM ".$table.$join['string'].$build['query'];
		$call = $this->connection->prepare($string);
		$call->execute($build['values']);
		$results= $call->fetchAll(PDO::FETCH_ASSOC);
		return ($results) ? $results[0] : $results;
	}
  	public function save($table,$data){
		$second = false;
		$values = [];
		$keys = $vars = '';
		foreach($data as $k=>$v)  {
			if($second) $keys.=",";
			if($second) $vars.=",";
			$keys.=$k;
			$vars.=':'.$k;
			$values[':'.$k]=$v;
	 	 	$second = true;
		}
		$string= "INSERT INTO ".$table." (".$keys.") VALUES ( ".$vars.")";
		$query = $this->connection->prepare($string);
		$query->execute($values);
		return $this->connection->lastInsertId();
  	}
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
		$query = $this->connection->prepare($q);
		$query->execute($values);
		return $query->rowCount();
  	}
  	public function updateSearch($table,$data,$query){
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
		$query = $this->connection->prepare($string);
		$query->execute($values);
		return $query->rowCount();
  	}
	public function count($table, $query=[]){
		$string = '';
		$values = [];
		if($query){
			$build = $this->buildWhere($query);
			$string = $build['query'];
			$values = $build['values'];
		}
		$string = "SELECT COUNT(*) FROM ".$table.$string;
		$query = $this->connection->prepare($string);
		$query->execute($values);
		return $query->fetchColumn();
	}
	public function delete($table,$id) {
		$string = "DELETE FROM ".$table." WHERE id=?";
		$values[] = $id;
		$query = $this->connection->prepare($string);
		$query->execute($values);
		return $query->rowCount();
	}
	public function search($table,$query=[],$fields=[],$max=10,$page=0,$sort='ASC',$order='id',$count=1) {
		$build = $this->buildWhere($query);
		$string = $build['query'];
		$values = $build['values'];
		//GET COUNT
		$query_count = ($count) ? $this->searchCount("SELECT Count(1) FROM ".$table.$string,$values) : null;
		if(!$max) ['count'=>$query_count,'hits'=>[]];
		//SET SORT AND ORDER
		$start = $page * $max;
		$string.=" ORDER BY ".$order." ".$sort;
		$string.=" LIMIT ".$start.",".$max;
		//PREPARE JOIN
		$join = $this->buildJoin($query,$fields);
		$string = "SELECT ".$join['fields']." FROM ".$table.$join['string'].$string;
		if($query){
			$call = $this->connection->prepare($string);
			$call->execute($values);
			$results= $call->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$call = $this->connection->query($string);
			$results = $call->fetchAll(PDO::FETCH_ASSOC);
		}
		return ['count'=>$query_count,'hits'=>$results];
	}
	public function searchCount($q,$values){
		$query = $this->connection->prepare($q);
		$query->execute($values);
		return $query->fetchColumn();
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
						$alias = str_replace('.','_',$f);
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
		$query=[];
		if(isset($data['and'])) $query['and'] = $data['and'];
		if(isset($data['or'])) $query['or'] = $data['or'];
		if(!$query) return ['query'=>'','values'=>[]];;
        $this->string = '';
        $this->values = [];
        $this->build($query);
		if($this->string!='') $this->string = ' WHERE '.$this->string;
        return ['query'=>$this->string,'values'=>$this->values];
    }
	public function build($data,$bool='AND'){
        if(!$this->assoc($data) && !$this->assoc($data[0])){
            $build = $this->buildWhereBlock($data,$bool);
            $this->string.=$build['string'];
            foreach($build['values'] as $v) $this->values[] = $v;
        } else {
            $count = count($data)-1;
            $prev_bool = $bool;
            $n=0;
            foreach($data as $key => $value){
                if($this->assoc($value)) {
                    $k = key($value);
                    $this->string.='(';
                    $this->build($value, strtoupper($k));
                    $last = ($count==$key) ? 1 :0;
                    if($last) $prev_bool = '';
                    $this->string.=') '.$prev_bool.' ';
                } else {
                    if($n) $this->string.=" ".strtoupper($key)." ";
                    $this->build($value, strtoupper($key));
                    $n++;
                }
            }
        }
    }
	public function buildWhereBlock($array,$bool='AND'){
        $second = false;
        $q='';
		$values = [];
       	foreach($array as $m){
           $key = $m[0];
           $action = strtoupper($m[1]);
           $val = '';
           if(isset($m[2])) {
               if($action=='IN'){
                   foreach($m[2] as $n=>$o){
                       $val.=($n) ? ',' : '(';
                       $values[]=$o;
                       $val.='?';
                   }
                   $val.=')';
               } elseif($action=='BETWEEN'){
                   $values[]=$m[2][0];
                   $values[]=$m[2][1];
                   $val='? AND ?';
               }else {
                   $values[]=$m[2];
                   $val='?';
               }
           }
           $q.= ($second) ? " ".$bool." ".$key." ".$action." ".$val : $key." ".$action." ".$val;
           $second = true;
       }
       return ['string'=>$q,'values'=>$values];
   }
}

?>
