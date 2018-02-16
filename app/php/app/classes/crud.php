<?php
class crud {
    public function __construct() {}
    public function get($get,$post=[]){
        if(!$this->auth($get,1)) return 0;
        return $this->mysql->get($get[0],$get[1]);
    }
    public function save($get,$post=[]) {
        if(!$this->auth($get)) return 0;
        return $this->mysql->save($get[0],$post);
    }
    public function update($get,$post=[]) {
        if(!$this->auth($get,1)) return 0;
        return $this->mysql->update($get[0],$get[1],$post);
    }
    public function count($get,$post=[]) {
        if(!$this->auth($get)) return 0;
        return $this->mysql->count($get[0],$post);
    }
    public function search($post,$get) {
        if(!$this->auth($get)) return 0;
        $type = $get[0] ?? '';
        $max =  $get[1] ?? 12;
        $page =  $get[2] ?? 0;
        $sort =  $get[3] ?? 'asc';
        $order =  $get[4] ?? 'id';
        return $this->mysql->search($type,$post,[],$max,$page,$sort,$order);
    }
    public function delete($post,$get) {
        if(!$this->auth($get,1)) return 0;
        return $this->mysql->delete($get[0],$get[1]);
    }
    public function auth($get,$n=0){
        if(!isset($get[0])) return 0;
        if($n>0 && !isset($get[1])) return 0;
        return 1;
        //WRITE CUSTOM AUTH FUNCTION
    }
}
?>
