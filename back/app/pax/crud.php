<?php
class crud {
    public $app;
    public function seed($get=[],$post=[]) {
    	$data = (isset($this->app->get('seed')->data[DB_NAME])) ? $this->app->get('seed')->data[DB_NAME] : [];
    	$map = (isset($this->app->get('seed')->map[DB_NAME])) ? $this->app->get('seed')->map[DB_NAME] : [];
        return $this->app->get(DB_CLASS)->seed(DB_NAME, $data, $map);
    }
    public function save($get=[],$post=[]) {
        $auth = $this->auth($get,$post,'save');
        if(!$auth) return ['error'=>1,'message'=>'You must be logged in'];
        $save = $this->app->get(DB_CLASS)->save($auth['get'][0],$auth['post']);
        return $save;
    }
    public function update($get=[],$post=[]) {
        if(!$this->auth($get,$post,'update')) return ['error'=>1,'message'=>'You must be logged in'];
        $update = $this->app->get(DB_CLASS)->update($get[0],$get[1],$post);
        return $update;
    }
    public function delete($get=[],$post=[]) {
        if(!$this->auth($get,$post,'delete')) return ['error'=>1,'message'=>'You must be logged in'];
        $del =  $this->app->get(DB_CLASS)->delete($get[1],$get[0]);
        return $del;
    }
    public function count($get=[],$post=[]) {
        if(!$this->auth($get,$post,'count')) return ['error'=>1,'message'=>'You must be logged in'];
        return $this->app->get(DB_CLASS)->count($get[0],$post);
    }
    public function get($get=[],$post=[]){
        if(!$this->auth($get,$post,'get')) return ['error'=>1,'message'=>'You must be logged in'];
        $key = (isset($get[2]) && $get[2]!='') ? [$get[2]=>$get[1]] : $get[1];
        return $this->app->get(DB_CLASS)->get($get[0],$key);
    }
    public function search($get=[],$post=[]) {
        if(!$this->auth($get,$post,'search')) return ['hits'=>[],'count'=>0,'error'=>1,'message'=>'No Access'];
        $type = (isset($get[0])) ? $get[0] : '';
        $max = (isset($get[1]) && is_numeric($get[1])) ? $get[1] : 10;
        $page = (isset($get[2])  && is_numeric($get[2])) ? $get[2] : 0;
        $sort = (isset($get[3])) ? $get[3] : 'asc';
        $order = (isset($get[4])) ? $get[4] : 'id';
        $count =  (isset($get[5])  && is_numeric($get[5])) ? $get[5] : 1;
        return $this->app->get(DB_CLASS)->search($type,$post,$max,$page,$sort,$order);
    }
    public function auth($get=[],$post=[],$method=''){
        //WRITE CUSTOM AUTH FUNCTION
        if(!isset($get[0])) return 0;
        $req = ["get","update","delete"];
        if(in_array($method,$req) && !isset($get[1])) return 0;
        return ['get'=>$get,'post'=>$post];
    }
}
?>
