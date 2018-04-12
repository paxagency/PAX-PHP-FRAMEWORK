<?php
/***************************************
****************************************
8888888b.     d8888Y88b   d88P
888   Y88b   d88888 Y88b d88P
888    888  d88P888  Y88o88P
888   d88P d88P 888   Y888P
8888888P" d88P  888   d888b
888      d88P   888  d88888b
888     d8888888888 d88P Y88b
888    d88P     888d88P   Y88b

PAX Unit Tests 1.0
Copyright 2018 PAX Agency & MIT Licensed
Created by Albert Kiteck
http://docs.paxagency.com/php/libraries/testing
****************************************
****************************************/
class unitTest {
    public $pass=0;
    public $fail=0;
    public function run($tests){
        $this->pass=0;
        $this->fail=0;
        $_start_total = microtime(true);
        $str = '';
        $n=1;
        foreach($tests as $class=>$methods){
            foreach($methods as $method=>$test){
                if(!$this->is_assoc($test)){
                    foreach($test as $t) {
                        if(isset($t['skip'])) continue;
                        $str.=$this->test($class,$method,$t,$n);
                        $n++;
                    }
                } else {
                    if(isset($test['skip'])) continue;
                    $str.=$this->test($class,$method,$test,$n);
                    $n++;
                }
            }
        }
        $n--;
        $_end_total = microtime(true);
        $_time_total = number_format(($_end_total - $_start_total),10);
        $_mem = (memory_get_peak_usage(true) / 1048576);
        $_memUsed = (ceil((memory_get_peak_usage(false) / 1048576)*100)/100);
        $percent = round($this->pass/$n*100);
        $errors = (!$this->fail) ? "<b style='color:green;'>PASSED: </b> {$n}/{$n} (100%)" : "<b style='color:red;'>FAILED</b>: ".$this->pass.'/'.$n.' ('.$percent.'%)';
        echo '<p><b>TIME:</b> '.$_time_total.' Seconds  <b>MEMORY:</b> '.$_memUsed.'MB/'.$_mem.'MB '.$errors.'</p><table>'.$str.'</table>';
    }
    public function test($class,$method,$test,$n=0){
        $_start = microtime(true);
        $val = (isset($test['vars'])) ? $this->$class->$method(...$test['vars']) : $this->$class->$method();

        $result = $this->result($test,$val);
        $_end = microtime(true);
        $_time = number_format(($_end - $_start),10);
        $str = ($n) ? '<tr><td>'.$n.'. </td>' : '<tr>';
        ($result) ? $this->pass++ : $this->fail++;
        $str .= ($result) ? '<td><span style="color:green;">PASSED</span>' : '<td><span style="color:red;">FAILED</span>';
        return $str.='</td><td> <b>$'.$class.'->'.$method.'</b></td><td>'.$test['message'].'</td><td> '.$_time.' Seconds</td></tr> ';
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
