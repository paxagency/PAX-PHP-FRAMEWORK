<?php
/****************************************
docs.paxagency.com/php/libraries/seed
*****************************************/
class seed {
	public $app;
    public $data = '';
    public $map = '';
    public $dbName = "";
    public function __construct() {
    	$this->dbName = DB_NAME;
    	if(file_exists(DIR_DB.'database.json')) {
			$this->data = json_decode($this->removeComments(file_get_contents(DIR_DB.'database.json')),true);
			$this->dbName = array_keys($this->data)[0];
		}
		if(file_exists(DIR_DB.'map.json')) {
			$this->map = json_decode($this->removeComments(file_get_contents(DIR_DB.'map.json')),true);
			if(!$this->map) $this->map = [];
        }
    }
    public function removeComments($input){
        $input =  preg_replace('#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#','$1',$input);
        return $input = preg_replace('/,[\n\s\t]*(?=[}\]])/','$1',$input);
    }
    public function seed($get=[],$post=[]) {
        if(!DB_CLASS || DB_CLASS=="seed" || !$this->app->get($this->DB_CLASS)) return ['error'=>'Database not assigned'];
        return $this->app->get($this->DB_CLASS)->seed($this->data[$this->dbName], $this->map[$this->dbName]);
    }
    public function update($type,$id,$post=[]) {
    	if(!count($post) || !isset($this->data[$this->dbName][$type]))  return ['Success'=>0,'Message'=>'Missing post fields'];
    	$set = 0;
    	foreach($this->data[$this->dbName][$type] as $i=>$row) {
    		if($row["id"]==$id) {
    			$set = 1;
    			foreach($post as $k=>$v) $this->data[$this->dbName][$type][$i][$k] = $v;
    		}
    	}
    	if(!$set) return ['Success'=>0,'Message'=>'Missing post fields'];
    	file_put_contents(DIR_DB.'database.json',  json_encode($this->data,JSON_PRETTY_PRINT));
        return ['Success'=>1,'Message'=>'Item saved'];
    }
    public function save($type,$post=[]) {
    	if(!count($post) || !isset($this->data[$this->dbName][$type]))  return ['Success'=>0,'Message'=>'Missing post fields'];
    	$post["id"] = "id".time().substr(uniqid('', true), -5);
    	$this->data[$this->dbName][$type][] = $post;
		file_put_contents(DIR_DB.'database.json',  json_encode($this->data,JSON_PRETTY_PRINT));
        return ['Success'=>1,'Message'=>'Item saved',"id"=>$post["id"]];
    }
    public function delete($type,$id) {
	    if(!isset($this->data[$this->dbName][$type]))  return ['Success'=>0,'Message'=>'Missing post fields'];
		$set = 0;
		foreach($this->data[$this->dbName][$type] as $i=>$row) {
			if($row["id"]==$id) {
				$set = 1;
				unset($this->data[$this->dbName][$type][$i]);
			}
		}
		if(!$set) return ['Success'=>0,'Message'=>'Missing post fields'];
		file_put_contents(DIR_DB.'database.json',  json_encode($this->data,JSON_PRETTY_PRINT));
        return ['Success'=>1,'Message'=>'Item deleted'];
    }
    public function setup($get=[],$post=[]) {
        if(!$this->app->get($this->DB_CLASS)) return ['error'=>'Database not injected'];
        return $this->app->get($this->DB_CLASS)->setup($this->map[$this->dbName]);
        return ['success'=>1];
    }
    public function get($type,$id){
    	if(is_array($id)) {
    		if(isset($this->data[$this->dbName][$type])) {
				foreach($this->data[$this->dbName][$type] as $n=>$t) {
					$pass = 1;
					$count = count($id)-1;
					$x = 0;
					foreach($id as $keys=>$ids) {
						$k = ($keys=='index') ? $n : $t[$keys];
						if(strtolower($k)!=strtolower($ids)) $pass = 0;
						if($x==$count && $pass) return $t;
						$x++;
					}
				}
			}
    	} else {
    		$key = 'id';
    		if(isset($this->data[$this->dbName][$type])) foreach($this->data[$this->dbName][$type] as $n=>$t) {
				$k = ($key=='index') ? $n : $t[$key];
				if($k==$id) return $t;
			}
    	}
        
        return ['success'=>0,'message'=>'No item was found'];
    }
    public function count($type,$post) {
        if($type=='') return  ["error"=>"Table not Found."];
        if(!isset($this->data[$this->dbName][$type])) return ["error"=>"Table not Found."];
        return $this->query($this->data[$this->dbName][$type],$post,0,0);
    }
    public function search($type,$post,$max,$page,$order="asc",$sort="id") {
        if($type=='' || $type=='_all') return $this->data;
        $array = [];
        $start = $page*$max;
        $end = $start+$max-1;
        $sort = str_replace("-",".",$sort);
        if(!isset($this->data[$this->dbName][$type])) return ["error"=>"Table not Found."];
        return $this->query($this->data[$this->dbName][$type],$post,$start,$end, $sort, $order);
    }
    public function query($data,$post,$start,$end,$sort,$order){
        $array1 = $array2 = [];
        $query = $post['query'] ?? [];
        
        if(!$post) {
        	$i = $start;
        	while($i<=$end) {
        		if(isset($data[$i])) $array2[]=$data[$i];
        		$i++;
        	}
        	if($sort) {
				foreach ($array2 as $o) $names[] = $this->nestedVal($o,$sort);
				array_multisort($names, SORT_ASC, $array2);
        	}
        	if($order!="desc") $array2 = array_reverse($array2);
        	return ['count'=>count($data),'hits'=>$array2];
        }
      
        foreach($data as $n=>$row) {
            $add = 0;
            
			if(isset($post['query'])) {
				foreach($row as $value) {
					if($this->contains($post['query'],$value)) $add = 1;
				}
			}
			if(isset($post['or'])) {
				$add = 0;
				foreach($post['or'] as $and) if($this->eq($and,$row)) $add=1;
			}
			if(isset($post['ids'])) {
				foreach($post['ids'] as $id) if($id==$row["id"]) $add=1;
			}
			if(isset($post['and'])) {
				if(!isset($post['query']) && !isset($post['or']) && !isset($post['ids'])) $add = 1;
				foreach($post['and'] as $and) if(!$this->eq($and,$row)) $add=0;
			}
			if($add) {
			 	$array1[] = $row;
			 	if($sort!="id") $names[] =  $this->nestedVal($row,$sort);
			}
        }
        
        if($sort!="id" && count($array1)) array_multisort($names, SORT_ASC, $array1);
        if($order!="desc") $array1 = array_reverse($array1);
        if(!$end) return ['count'=>count($array1)];
        $i = $start;
        while($i<=$end) {
        	if(isset($array1[$i])) $array2[]=$array1[$i];
        	$i++;
        }
        return ['count'=>count($array1),'hits'=>$array2];
    }
    public function eq($a,$row){
    	
        switch ($a[1]) {
            case "=":
                return $this->nestedVal($row,$a[0])==$a[2];
            case "==":
                return $this->nestedVal($row,$a[0])==$a[2];
            case "!=":
                return $this->nestedVal($row,$a[0])!=$a[2];
            case ">":
                return $this->nestedVal($row,$a[0])>$a[2];
            case ">":
                return $this->nestedVal($row,$a[0])<$a[2];
            case "exists":
                return ($this->nestedVal($row,$a[0])) ? 1 :0 ;
            case "missing":
                return ($this->nestedVal($row,$a[0])) ? 0 : 1;
        }
    }
    public function nestedVal($row,$field){
        if(!$this->contains('.',$field)) return (isset($row[$field])) ? $row[$field] : 0;
        $exp = explode('.',$field);
        foreach($exp as $ex) $row = (isset($row[$ex])) ? $row[$ex] : 0;
        return $row;
    }
    public function contains($needle, $haystack){
        if($needle=='' ) return true;
        if(!$haystack || is_array($haystack) || $haystack=="") return false;
        return strpos(strtolower($haystack), strtolower($needle)) !== false;
    }
}
?>
