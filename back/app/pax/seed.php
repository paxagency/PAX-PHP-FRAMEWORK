<?php
/****************************************
docs.paxagency.com/php/libraries/seed
*****************************************/
class seed {
	public $app;
    public $data = '';
    public $map = '';
    public $dbName = "";
    public $dbClass = "db";
    public function __construct() {
    	if(DB_CLASS=="seed") {
			$this->data = json_decode($this->removeComments(file_get_contents(DIR_DB.'database.json')),true);
			$this->map = json_decode($this->removeComments(file_get_contents(DIR_DB.'map.json')),true);

			if(!$this->map) $this->map = [];
		
			$this->dbName = array_keys($this->data)[0]; //array_key_first($this->data);
        }
    }
    public function removeComments($input){
    	//comments
        $input =  preg_replace('#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#','$1',$input);
        //trailing commas
        return $input = preg_replace('/,[\n\s\t]*(?=[}\]])/','$1',$input);
    }
    public function seed($get=[],$post=[]) {
        if(!$this->app->get($this->dbClass)) return ['error'=>'Database not injected'];
        return $this->map[$this->dbName];
        return $this->app->get($this->dbClass)->seed($this->data[$this->dbName], $this->map[$this->dbName]);
    }
    public function update($get=[],$post=[]) {
        return ['Success'=>0,'Message'=>'Item is not updated. This requires a database.'];
    }
    public function save($get=[],$post=[]) {
        return ['Success'=>0,'Message'=>'Item is not saved. This requires a database.'];
    }
    public function delete($get=[],$post=[]) {
        return ['Success'=>0,'Message'=>'Item is not deleted. This requires a database.'];
    }
    public function setup($get=[],$post=[]) {
        if(!$this->app->get($this->dbClass)) return ['error'=>'Database not injected'];
        return $this->app->get($this->dbClass)->setup($this->map[$this->dbName]);
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
    public function search($type,$post,$max,$page,$sort,$order) {
        if($type=='' || $type=='_all') return $this->data;
        $array = [];
        $start = $page*$max;
        $end = $start+$max-1;
        
        if(!isset($this->data[$this->dbName][$type])) return ["error"=>"Table not Found."];
        return $this->query($this->data[$this->dbName][$type],$post,$start,$end);
    }
    public function query($data,$post,$start,$end){
        $array1 = $array2 = [];
        $query = $post['query'] ?? [];
        
        if(!$post) {
        	$i = $start;
        	while($i<=$end) {
        		if(isset($data[$i])) $array2[]=$data[$i];
        		$i++;
        	}
        	return ['count'=>count($data),'hits'=>$array2];
        }
        foreach($data as $n=>$row) {
            $add = 1;
            if(isset($post['query'])) {
                $add = 0;
                foreach($row as $value) {
                    if($this->contains($post['query'],$value)) $add = 1;
                }
                if($add) $array1[] = $row;
            }
            if(isset($post['and'])) {
                $add = 1;
                foreach($post['and'] as $and) if(!$this->eq($and,$row)) $add=0;
                if($add)  $array1[] = $row;
            }
           
            if(isset($post['or'])) {
                $add = 0;
                foreach($post['or'] as $and) if($this->eq($and,$row)) $add=1;
                if($add) $array1[] = $row;
            }
             if(isset($post['ids'])) {
             	 $add = 0;
             	foreach($post['ids'] as $id) if($id==$row["id"]) $add=1;
                if($add) $array1[] = $row;
             }
        }
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
