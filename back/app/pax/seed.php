<?php
/****************************************
docs.paxagency.com/php/libraries/seed
*****************************************/
class seed {
    private $data = '';
    private $map = '';
    public function __construct() {
        $this->data = json_decode($this->removeComments(file_get_contents(DIR_DB.'database.json')),true);
        $this->data =  $this->data[DB_NAME];
        $this->map = json_decode($this->removeComments(file_get_contents(DIR_DB.'map.json')),true);
    }
    public function removeComments($input){
        return preg_replace('#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#','$1',$input);
    }
    public function seed($get=[],$post=[]) {
        if(!isset($this->db)) return ['error'=>'Database not injected'];
        foreach($this->map['tables'] as $t=>$o) {
            if(isset($this->data[$t])) $this->db->save($t,$this->data[$t]);
        }
        return ['success'=>1];
    }
    public function setup($get=[],$post=[]) {
        if(!isset($this->db) || !$this->db->connection) return ['error'=>'Database not injected'];
        $this->db->setup($this->map);
        return ['success'=>1];
    }
    public function generate($get=[],$post=[]) {
        if(!isset($this->db) || !$this->db->connection)  return ['error'=>'Database not connected'];
        $this->db->setup($this->map);
        foreach($this->map['tables'] as $t=>$o) {
            if(isset($this->data[$t])) $this->db->save($t,$this->data[$t]);
        }
        return ['success'=>1];
    }
    public function get($get=[],$post=[]){
        $key = (isset($get[2]) && $get[2]!='') ? $get[2] : 'id';
        if(isset($this->data[$get[0]])) foreach($this->data[$get[0]] as $t) if($t[$key]==$get[1]) return $t;
        return ['success'=>0,'message'=>'No item was found'];
    }
    public function search($get=[],$post=[]) {
        if(!isset($get[0])) return $this->data;
        $type = $get[0] ?? '';
        $max = $get[1] ?? 12;
        $page = $get[2] ?? 0;
        $array = [];
        $start = $page*$max;
        $end = $start+$max-1;
        if(!isset($this->data[$type])) return ["error"=>"Table not Found."];
        foreach($this->data[$type] as $n=>$t) {
            if($n>=$start && $n<=$end){
                $array[]=$t;
            }
        }
        return $array;
    }
}
?>
