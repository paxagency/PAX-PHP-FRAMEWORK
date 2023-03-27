<?php

class auth {
    public $tokenLength = 10;
    public $tokenTime = 9200; //20 minutes
    public $testMode = 0;
    public $app;
    public function login($get=[],$post=[]) { 
    	//ATTEMPTS
    	if(!isset($this->app->get("session")->data['attempts'])) $this->app->get("session")->data['attempts']=0;
    	$this->app->get("session")->data['attempts']++;
    	if($this->app->get("session")->data['attempts']>8) return ['success'=>0,'url'=>SITE_PUBLIC.'error/attempts'];
    	//AUTH
    	if($this->authorize($post)) {
            return ['success'=>1,'url'=>SITE_PRIVATE];
        } else {
            return ['success'=>0,'url'=>SITE_PUBLIC.'error'];
        }
    }
    private function authorize($post) {
    	if($this->testMode) {
    		return $this->saveToken([
    			'id'=>"ID01",
                'first_name'=>"John",
                'last_name'=>"Doe",
                'text'=>"John Doe",
                'email'=>"john_doe@website.com",
                'admin'=>1
			]);
    	}
        $user = $this->getUser('email',$post['email']);
        if(!$user || !isset($user['active'])) return 0;
        return (password_verify($post['password'], $user['password']))
             ? $this->saveToken($user) : 0;
    }
    public function save($get=[],$post=[]) {
    	if(isset($post["captcha"])) {
    		if(!$this->checkCaptcha($post["captcha"])) return ['success'=>0,'message'=>'You did not pass captcha.','url'=>SITE_PUBLIC.'thankyou/error/captcha'];
    	}
    	
        $post = $this->setUserData($post);
        if(!$post) return ['success'=>0,'url'=>SITE_PUBLIC.'thankyou/error'];
      
        $user = $this->getUser('email',$post['email']);
        if(!$user['success']) return ['success'=>0,'message'=>'This user already exists.','url'=>SITE_PUBLIC.'thankyou/error'];
        
        $save = $this->saveUser($post);
        $url = SITE_PUBLIC."api/auth/verify/".$post['token'].'?post=1';
        $this->sendVerify($post['email'],$url);
        return ['success'=>1,'url'=>SITE_PUBLIC.'thankyou/new'];
    }
    public function logout($get=[],$post=[]) {
        $this->app->get("session")->destroy();
        return ['success'=>1,'url'=>SITE_PUBLIC];
    }
    public function reset($get=[],$post=[]) {
        $user = $this->getUser('email',$post['email']);
        if(!$user['success'])  return ['success'=>0,'url'=>SITE_PUBLIC."thankyou/reset"];
        $send = $this->sendReset($post['email'],$user['token']);
        return ['success'=>1,'url'=>SITE_PUBLIC."thankyou/reset"];
    }
    public function set($get=[],$post=[]) {
        $user = $this->getUser('token',$post['token']);
        if(!$user) return ['success'=>0,'url'=>SITE_PUBLIC.'thankyou/error'];
        $password = $post['password'];
        $pass = $this->newPassword($password);
        $update = $this->saveUser(['password'=>$pass,'token'=>$this->genToken()],$user);
        return ['success'=>1,'url'=>SITE_PUBLIC.'thankyou/set'];
    }
    public function resend($get,$post){
        $user = $this->getUser('email',$post['email']);
        if(!$user || $user['active']) return ['success'=>0,'url'=>SITE_PUBLIC.'thankyou/resend'];
        $url = SITE_PUBLIC."verify/".$user['token'];
        $this->sendVerify($post['email'],$url);
        return ['success'=>1,'url'=>SITE_PUBLIC.'thankyou/resend'];
    }
    private function setUserData($o){
        $required = ['email','password','first_name','last_name'];
        foreach($required as $r) if(!isset($o[$r])) return false;
        $post['email'] = strtolower($o['email']);
        $post['password'] = $this->newPassword($o['password']);
        if(isset($post['newsletter'])) $post['newsletter'] = $o['newsletter'];
        $post['created']=gmdate("Y-m-d\TH:i:s");
        $post['first_name']=$o['first_name'];
        $post['last_name']=$o['last_name'];
        $post['token']=$post['user_token']=$this->genToken();
        $post['active'] = 0;
        if(isset($this->app->get("session")->data['cookie']["refer"])) {
        	$user = $this->getUser('user_token',$this->app->get("session")->data['cookie']["refer"]);
        	if($user) $post['affiliate']=["text"=>$user["first_name"].' '.$user["last_name"],"id"=>$user["id"],"refer"=>$this->app->get("session")->data['cookie']["refer"]];
        }
        return $post;
    }
    private function newPassword($password) {
        return password_hash($password,PASSWORD_BCRYPT);
    }
    public function verify($get=[],$post=[]) {
        $user = $this->getUser('token',$get[0]);
    	if(!$user) return ['success'=>0,'url'=>SITE_PUBLIC];
        $user = $this->saveUser(['active'=>1,'token'=>$this->genToken()],$user);
        return ['success'=>1,'url'=>SITE_PUBLIC.'thankyou/verify'];
    }
    public function status($get=[],$post=[]) {
        $user = $this->getUser('token',$get[0]);
    	if(!$user) return ['success'=>0,'url'=>SITE_PUBLIC];
        $user = $this->saveUser(['status'=>1],$user);
        return ['success'=>1,'url'=>SITE_PUBLIC.'thankyou/verify'];
    }
    private function saveToken($user){
    	if(!isset($user['user_token'])) {
    		 $user['user_token'] = $this->genToken();
    		 $this->app->get("crud")->update(['user',$user['id']],["user_token"=>$user['user_token']]);
    	}
    	
        return $this->app->get("session")->data['token'] = $this->app->get("token")->encode([
            'exp'=>time()+$this->tokenTime,
            'user'=>[
                'id'=>$user['id'],
                'text'=>$user['first_name'].' '.$user['last_name'],
                'email'=>$user['email'],
                'first_name'=>$user['first_name'],
                'last_name'=>$user['last_name'],
                'user_token'=>$user['user_token']
            ]
        ]);
    }
    public function verifyToken($user){
    	$token = (isset($this->app->get("session")->data['token'])) ? $this->app->get("session")->data['token'] : "";
        $token = $this->app->get("token")->decode($token);
        return (!$token['success'] || time()>$token['exp']) ? 0 : 1;
    }
    public function getToken(){
    	$token = (isset($this->app->get("session")->data['token'])) ? $this->app->get("session")->data['token'] : "";
        $token = $this->app->get("token")->decode($token);
  		return (isset($token["user"])) ? $token["user"] : 0;
    }
    //EMAILS
    private function sendVerify($email,$url){
        return $this->mailer->send([
            'to'=>$email,
            'subject'=>'Account Created!',
            'body'=>"<img src='https://clearityhealth.com/public/img/fam2.jpg' style='max-width:400px;'/><h1>Welcome to Clearity</h1><p>Your account has been created. <br />please <a href='".$url."'>Click Here</a> to login to your brand new account! </p>"
        ]);
    }
    private function sendReset($email,$email_key){
        $url = SITE_PUBLIC."account/set/".$email_key;
        $msg = "<h1>Password Reset</h1><p>To reset your password please click the link below</p><p><a href='".$url."'>".$url."</a></p>";
        return $this->mailer->send(['to'=>$email,'subject'=>"Password Reset",'body'=>$msg]);
    }
    private function genToken(){
        return bin2hex(random_bytes($this->tokenLength));
    }
    private function checkCaptcha($token) {
		$url = "https://www.google.com/recaptcha/api/siteverify?secret=".CAPTCHA_SECRET."&response=".$token;
		$request = file_get_contents($url);
		$response = json_decode($request);
		return $response->success;
	}
    //DATABASE FUNCTIONS
    private function getUser($key,$var){
        $var = strtolower(trim($var));
        $user = $this->app->get("crud")->get(['user',$var,$key]);
        return ($user) ? $user: 0;
    }
    private function saveUser($post,$user=0){
        return (!$user) ?  $this->app->get("crud")->save(['user'],$post) :  $this->app->get("crud")->update(['user',$user['id']],$post);
    }
    
}