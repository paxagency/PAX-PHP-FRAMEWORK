<?php
class mailer {
	public $app;
    public $required = ["from","body"]; 
    public $from = "info@website.com";
    public $bg = "#888888";
    public $company = "My Company";
    public $company_image = "";
    public $website = "https://website.com";
    public $phone = "555.555.5555";
    public function __construct() {
     	if(isset(EM_FROM)) $this->from = EM_FROM;
    }
    public function send($post){
    	foreach($this->required as $r) if(!isset($post[$r])) return ["success"=>0,"message"=>"Missing fields"];
        return  (!EM_CLASS) ? $this->sender($post) : $this->app->get($this->active)->send($post);
    }
    public function sender($post=[]) {
        $post['from'] =  $this->from;
        if($post['type']!='text'){
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: Website <'.$post['from'].'>' . "\r\n";
            $post['body'] = $this->html($post['body']);
        } else {
            $headers = "From: ".$post['from'];
        }
        return mail($post['to'][0],$post['subject'],$post['body'],$headers);
    }
    public function html($html){
    	$company = ($this->company_image) ? "<img src='".$this->company_image."' alt='".$this->company."' style='width:100%;padding:40px;' />" : $this->company;
        return "<body style='background:#FAFAFA;margin:0;font-family:arial;color:#555;padding-top:50px;'>
        <table style='width:100%;'><tr>
            <td></td>
            <td style='width:665px; padding:40px;font-size:16px; line-height:1.7em; background:".$this->bg.";border:solid thin #ddd; color:#555;font-family:Calibri,Helvetica,arial,sans-serif;text-align:center;'>
                <h1 style='text-align:center;padding:0 0 30px 0;margin:0;'>".$company."</h1>".$html."
              	<p>".$this->phone." <br />
                <a href='mailto:".$this->from."'>".$this->from."</a><br />
                <a href='".$this->website."'>".$this->website."</a><br />
    			<a href='".$this->website."/terms'>Terms & Conditions</a></p>
            </td>
            <td></td>
        </tr>
        </table>
    </body>";
    }
    public function test(){
        return $this->send([
            'to'=>'my_email@website.com',
            'body'=>'<h1>Hello</h1><p>Just seeing if this works. thanks</p>'
        ]);
    }
}
?>
