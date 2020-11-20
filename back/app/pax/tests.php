<?php
/****************************************
docs.paxagency.com/php/libraries/tests
*****************************************/
class tests {
    public $app;
    public $pass=0;
    public $fail=0;
    public $html='';
    public $cli='';
    public function __construct() {}
    public function run($data){
        $this->html = '';
        $this->cli = '';
        $this->pass=0;
        $this->fail=0;
        $time_start = microtime(true);
        $n=1;
        foreach($data as $class=>$methods){
            foreach($methods as $method=>$tests){
                if(!$this->is_assoc($tests)){
                    foreach($tests as $test) {
                        if(isset($test['skip'])) continue;
                        $this->test($class,$method,$test,$n);
                        $n++;
                    }
                } else {
                    if(isset($tests['skip'])) continue;
                    $this->test($class,$method,$tests,$n);
                    $n++;
                }
            }
        }
        $n--;
        $this->render($time_start,$n);
    }
    public function render($time_start,$n){
        $time_end = microtime(true);
        $time_total = number_format(($time_end - $time_start),10);
        $memory_total = (memory_get_peak_usage(true) / 1048576);
        $memory_used = (ceil((memory_get_peak_usage(false) / 1048576)*100)/100);
        $percent = round($this->pass/$n*100);

        $errors = (!$this->fail) ? "<b style='color:green;'>PASSED: </b> {$n}/{$n} (100%)" : "<b style='color:red;'>FAILED</b>: ".$this->pass.'/'.$n.' ('.$percent.'%)';
        $this->html = '<p><b>TIME:</b> '.$time_total.' Seconds  <b>MEMORY:</b> '.$memory_used.'MB/'.$memory_total.'MB '.$errors.'</p><table>'.$this->html.'</table>';
        $this->html.="<style> table{width:100%;}tr:nth-child(even){background:#f3f3f3;}td {padding:7px 15px;}body{font-family:Consolas,Monaco,Lucida Console,Courier New;}</style>";
        $errors = (!$this->fail) ? "\e[42mPASSED\e[0m {$n}/{$n} (100%)" : "\e[1;37;41mFAILED\e[0m ".$this->pass.'/'.$n.' ('.$percent.'%)';
        $this->cli = PHP_EOL.'TIME: '.$time_total.' Seconds MEMORY: '.$memory_used.'MB/'.$memory_total.'MB '.$errors.PHP_EOL.$this->cli.PHP_EOL.PHP_EOL;
    }
    public function test($class,$method,$test,$n=0){
        $_start = microtime(true);
        $val = (isset($test['vars'])) ? $this->app->get($class)->$method(...$test['vars']) : $this->app->get($class)->$method();
        $result = $this->result($test,$val);
        $_end = microtime(true);
        $_time = number_format(($_end - $_start),10);
        ($result) ? $this->pass++ : $this->fail++;

        $this->html.= ($n) ? '<tr><td>'.$n.'. </td>' : '<tr>';
        $this->html.= ($result) ? '<td><span style="color:green;">PASSED</span>' : '<td><span style="color:red;">FAILED</span>';
        $this->html.= '</td><td> <b>$'.$class.'->'.$method.'</b></td><td>'.$test['message'].'</td><td> '.$_time.' Seconds</td></tr> ';
        
        $this->cli.= ($n) ? PHP_EOL.$n.') ' : PHP_EOL;
        $this->cli.= ($result) ? " PASSED " : "\e[1;37;41mFAILED\e[0m ";
        $this->cli.= '$'.$class.'->'.$method.' | '.$test['message'].' | '.$_time.' Seconds';
    }
    public function result($data,$val){
        if(isset($data['and'])) return ($this->and($data['and'],$val)) ? 1 :0;
        if(isset($data['or'])) return ($this->or($data['or'],$val)) ? 1 :0;
    	if(isset($data['func'])){
            $result = $data['func_result'] ?? true;
    		$test = $data['func'];
    		if(isset($data['func_vars'])) {
    		  	foreach($data['func_vars'] as $n=>$t) {
                    if($t=='_val') $data['func_vars'][$n] = $val;
                    if($t=='_session') $data['func_vars'][$n] = $this->session->data;
                }
    		  	return ($test(...$data['func_vars'])==$result) ? 1 : 0;
    		} else {
    		    return ($test($val)==$result) ? 1 :0;
    		}
    	}
    }
    public function or($o,$val){
        $pass = 0;
        if(isset($o[0]['and']) || isset($o[0]['or'])){
            foreach($o as $if){
                $k = key($if);
                if($k=='and') if($this->and($if['and'],$val)) $pass = 1;
                if($k=='or') if($this->or($if['or'],$val)) $pass = 1;
            }
        } else {
            foreach($o as $if)if($this->if($if,$val)) $pass = 1;
        }
        return $pass;
    }
    public function and($o,$val){
        $pass = 1;
        if(isset($o[0]['and']) || isset($o[0]['or'])){
            foreach($o as $if){
                $k = key($if);
                if($k=='and') if(!$this->and($if['and'],$val)) $pass = 0;
                if($k=='or') if(!$this->or($if['or'],$val)) $pass = 0;
            }
        } else{
            foreach($o as $if) if(!$this->if($if,$val)) $pass = 0;
        }
        return $pass;
    }
    public function if($o,$v){
        switch($o[0]){
            case '==':
                return($v == $o[1]);
                break;
            case '!=':
                return($v != $o[1]);
                break;
            case '>':
                return($v > $o[1]);
                break;
            case '>=':
                return($v >= $o[1]);
                break;
            case '<':
                return($v < $o[1]);
                break;
            case '<=':
                return($v <= $o[1]);
                break;
            case '===':
                return($v === $o[1]);
                break;
            case '!==':
                return($v !== $o[1]);
                break;
            default:
                return false;
                break;
        }
    }
    private function is_assoc($array){
       $keys = array_keys($array);
       return $keys !== array_keys($keys);
    }
}
