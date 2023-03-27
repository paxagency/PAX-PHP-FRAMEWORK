<?php
class control {
	public $app;
    public function config($get=[],$post=[]) {
       	return $this->verifyUser();
    }
    public function removeComments($input){
    	//comments
        $input =  preg_replace('#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#','$1',$input);
        //trailing commas
        return $input = preg_replace('/,[\n\s\t]*(?=[}\]])/','$1',$input);
    }
    public function verifyUser(){
    	if(isset($this->data)) return $this->data;
    	$t = (isset($this->app->get("session")->data['token'])) ? $this->app->get("session")->data['token'] : 0;
        $token = $this->app->get("token")->decode($t);
        $this->data = (!$token['success'] || time()>$token['exp']) ? ["user"=>["error"=>"not logged"],"logged"=>0] : ['user'=>$token['user'],"logged"=>1];
        return $this->data;
    }
}

?>
