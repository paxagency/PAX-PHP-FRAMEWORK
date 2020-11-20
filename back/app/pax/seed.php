<?php
/****************************************
docs.paxagency.com/php/libraries/seed
*****************************************/
class seed {
    public $data = '';
    public $map = '';
    public $dbName = "";
    public function __construct() {
        $this->data = json_decode($this->removeComments(file_get_contents(DIR_DB.'database.json')),true);
        $this->map = json_decode($this->removeComments(file_get_contents(DIR_DB.'map.json')),true);
        $this->dbName = DB_NAME;
    }
    public function removeComments($input){
        return preg_replace('#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#','$1',$input);
    }
    public function seed($get=[],$post=[]) {
        if(!isset($this->db)) return ['error'=>'Database not injected'];
        foreach($this->map['tables'] as $t=>$o) {
            if(isset($this->data[$this->dbName][$t])) $this->db->save($t,$this->data[$this->dbName][$t]);
        }
        return ['success'=>1];
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
        if(!isset($this->db)) return ['error'=>'Database not injected'];
        $this->db->setup($this->map);
        return ['success'=>1];
    }
    public function generate($get=[],$post=[]) {
        if(!isset($this->db)) return ['error'=>'Database not injected'];
        $this->db->setup($this->map);
        foreach($this->map['tables'] as $t=>$o) {
            if(isset($this->data[$this->dbName][$t])) $this->db->save($t,$this->data[$this->dbName][$t]);
        }
        return ['success'=>1];
    }
    public function get($type,$id,$key='id'){
        if(isset($this->data[$this->dbName][$type])) foreach($this->data[$this->dbName][$type] as $n=>$t) {
            $k = ($key=='index') ? $n : $t[$key];
            if($k==$id) return $t;
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
        
        foreach($data as $n=>$row) {
            $add = 1;
            if(isset($post['query'])) {
                $add = 0;
                foreach($row as $value) {
                    if($this->contains($post['query'],$value)) $add = 1;
                }
            }
            if(isset($post['and'])) {
                $add = 1;
                foreach($post['and'] as $and) if(!$this->eq($and,$row)) $add=0;
            }
            if($add)  $array1[] = $row;
        }
        if(!$end) return ['count'=>count($data)];
        foreach($array1 as $n=>$t) {
            if($n>=$start && $n<=$end){
                $array2[]=$t;
            }
        }
        return ['count'=>count($data),'hits'=>$array2];
    }
    public function eq($a,$row){
        switch ($a[1]) {
            case "=":
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
        if(!$this->contains('.',$field)) return $row[$field];
        $exp = explode('.',$field);
        foreach($exp as $ex) $row = (isset($row[$ex])) ? $row[$ex] : 0;
        return $row;
    }
    public function contains($needle, $haystack){
        if($needle=='') return true;
        if(is_array($haystack)) return false;
        return strpos(strtolower($haystack), strtolower($needle)) !== false;
    }
}
?>
