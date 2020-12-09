<?php
class crud {
    public $app;
    public $active = 'seed';  
    public function seed($get=[],$post=[]) {
        return $this->app->get($this->active)->seed(DB_NAME, $this->app->get('seed')->data[DB_NAME], $this->app->get('seed')->map[DB_NAME]);
    }
    public function save($get=[],$post=[]) {
        $auth = $this->auth($get,$post,'save');
        if(!$auth) return ['error'=>1,'message'=>'You must be logged in'];
        $save = $this->app->get($this->active)->save($auth['get'][0],$auth['post']);
        return $save;
    }
    public function update($get=[],$post=[]) {
        if(!$this->auth($get,$post,'update')) return ['error'=>1,'message'=>'You must be logged in'];
        $update = $this->app->get($this->active)->update($get[0],$get[1],$post);
        return $update;
    }
    public function delete($get=[],$post=[]) {
        if(!$this->auth($get,$post,'delete')) return ['error'=>1,'message'=>'You must be logged in'];
        $del =  $this->app->get($this->active)->delete($get[1],$get[0]);
        return $del;
    }
    public function count($get=[],$post=[]) {
        if(!$this->auth($get,$post,'count')) return ['error'=>1,'message'=>'You must be logged in'];
        return $this->app->get($this->active)->count($get[0],$post);
    }
    public function get($get=[],$post=[]){
        if(!$this->auth($get,$post,'get')) return ['error'=>1,'message'=>'You must be logged in'];
        $key = (isset($get[2]) && $get[2]!='') ? $get[2] : 'id';
        return $this->app->get($this->active)->get($get[0],$get[1],$key);
    }
    public function search($get=[],$post=[]) {
        if(!$this->auth($get,$post,'search')) return ['hits'=>[],'count'=>0,'error'=>1,'message'=>'No Access'];
        $type = $get[0] ?? '';
        $max = $get[1] ?? 10;
        $page = $get[2] ?? 0;
        $sort = $get[3] ?? 'asc';
        $order = $get[4] ?? 'id';
        return $this->app->get($this->active)->search($type,$post,$max,$page,$sort,$order);
    }
    public function select($get=[],$post=[]) {
        if(!$this->auth($get,$post,'search')) return ['hits'=>[],'count'=>0,'error'=>1,'message'=>'No Access'];
        $type = $get[0] ?? '';
        $key = $get[1] ?? 'name';
        $keyId = $get[2] ?? 'id';
        $search = $this->search([$type,1000],$post);
        $array =  [['id'=>'','text'=>'']];
        foreach($search['hits'] as $n=>$t) {
            $val = "";
            $exp = explode(",",$key);
            foreach($exp as $i=>$e) $val.=($i) ? " ".$t[$e] : $t[$e];
            $id = (isset($t[$keyId])) ? $t[$keyId] : $n;
            $array[]=['id'=>$id,'text'=>$val];
        }
        return $array;   
    }
    public function auth($get=[],$post=[],$method=''){
        //WRITE CUSTOM AUTH FUNCTION
        if(!isset($get[0])) return 0;
        return ['get'=>$get,'post'=>$post];
    }
}
?>
