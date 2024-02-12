<?php
class mailer {
    private $from = "info@paxagency.com";
    public $type = "html";
    public $color = "white";
    public $company = "My company";
    public $website = "https://www.website.com";
    public $logo = "https://www.website.com/logo.png";
    public $mail_class = "";  
    public $app;
    public function send($post){
        return  ($this->mail_class!="") ? $this->app->get($this->mail_class)->send($post) : $this->sender($post);
    }
    public function sender($post=[]) {
        $post['from'] =  (isset($post['from'])) ? $post['from'] : $this->from;
        $post['type'] = (isset($post['type'])) ? $post['type'] : $this->type;
        if($post['type']!='text'){
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: Website<'.$post['from'].'>' . "\r\n";
            $post['body'] = $this->html($post['body']);
        } else {
            $headers = "From: ".$post['from'];
        }
        return mail($post['to'][0],$post['subject'],$post['body'],$headers);
    }
    public function html($html){
        return "<body style='background:#FAFAFA;margin:0;font-family:arial;color:#555;padding-top:50px;'>
				<table style='width:100%;'><tr>
					<td></td>
					<td style='width:665px; padding:40px;font-size:16px; line-height:1.7em; background:".$this->color.";border:solid thin #ddd; color:#555;font-family:Calibri,Helvetica,arial,sans-serif;text-align:center;'>
						<h1 style='text-align:center;padding:0 0 30px 0;margin:0;'><img src='".$this->logo."' alt='".$this->company."' style='width:100%;' /></h1>
					  ".$html."
						<p>615-544-5006 <br />
						<a href='mailto:".$this->from."'>".$this->from."</a><br />
						<a href='".$this->website."'>".$this->website."</a><br />
						<a href='".$this->website."/terms'>Terms & Conditions</a></p>
					</td>
					<td></td>
				</tr>
				</table>
			</body>";
    }
}
?>
