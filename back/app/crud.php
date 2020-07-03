<?php
class crud {
    public function __construct() {}
    public function save($get=[],$post=[]) {
        if(!$this->auth($get)) return 0;
        return $this->db->save($get[0],$post);
    }
    public function update($get=[],$post=[]) {
        if(!$this->auth($get)) return 0;
        return $this->db->update($get[0],$get[1],$post);
    }
    public function delete($get=[],$post=[]) {
        if(!$this->auth($get)) return 0;
        return $this->db->delete($get[0],$get[1]);
    }
    public function count($get=[],$post=[]) {
        if(!$this->auth($get)) return 0;
        return $this->db->count($get[0],$post);
    }
    public function get($get=[],$post=[]){
        if(!$this->auth($get)) return ['error'=>'No Access'];
        return $this->db->get($get[0],$get[1]);
    }
    public function search($get=[],$post=[]) {
        if(!$this->auth($get)) return ['hits'=>[],'count'=>0,'error'=>'No Access'];
        $type = $get[0] ?? '';
        $max = $get[1] ?? 12;
        $page = $get[2] ?? 0;
        $sort = $get[3] ?? 'asc';
        $order = $get[4] ?? 'id';
        return $this->db->search($type,$post,$max,$page,$sort,$order);
    }
    public function auth($get=[]){
        //WRITE CUSTOM AUTH FUNCTION
        if(!isset($get[0])) return 0;
        return 1;
    }
}
?>
