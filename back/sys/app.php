<?php
/*********************
docs.paxagency.com/php
*********************/
#[AllowDynamicProperties]
class app {
    public $_folder = [];
    private $_passApp = true;
	public function __construct() {
        $this->_setFolder(DIR_APP);
    }
	public function get($class){
		if(!isset($this->$class) && isset($this->_folder[$class])) {
			require_once($this->_folder[$class]);
			$this->$class = ($this->_passApp && $this->_hasArg($class)) ? new $class($this) : new $class();
			if(property_exists($class,'app')) $this->$class->app = $this;
			if(property_exists($class,'inject')) {
				foreach($this->$class->inject as $inject) {
					$this->$class->$inject = $this->get($inject);
				}
			}
		}
		return (isset($this->$class)) ? $this->$class : [];
	}
	private function _setFolder($dir) {
		$folders = [];
	  	foreach(scandir($dir) as $file) {
			if($file[0]==".") continue;
			if(is_dir($dir.$file)) {
				$folders[] = $dir.$file;
			} else {
				$name = str_replace('.php','',$file);
				$this->_folder[$name] = $dir.$file;
			}
	  	}
	  	foreach($folders as $f) $this->_setFolder($f.'/');
	}
	private function _hasArg($class){
		$reflector = new ReflectionClass($class);
		$constructor = $reflector->getConstructor();
		if ($constructor && $constructor->getParameters()) 
			return ($constructor->getParameters()[0]->name=="app") ? 1 : 0; 
		return 0;
	}
	public function urlMode($a=[]){
		global $_PATH;
		$str = substr($_PATH, 0, -5);
		(isset($a[$str])) ? $this->urlVars(...$a[$str]) : $this->urlVars();
	}
	public function urlVars($n=0,$strict=0) {
		global $_URL_VARS;
	 	$pass = true;
		if(isset($_URL_VARS[$n]) && $_URL_VARS[$n]!="") $pass = false;
		if($strict && !isset($_URL_VARS[1]) && (!isset($_URL_VARS[0]) || $_URL_VARS[0]=="")) $pass = false;
	    if(!$n && ((isset($_URL_VARS[0]) && $_URL_VARS[0]!="") || isset($_URL_VARS[1]))) $pass = false;
		if($strict==2 && (!isset($_URL_VARS[$n-1]) || $_URL_VARS[$n-1]=="")) $pass = false;
		
		if(!$pass) {
			require_once(DIR_PAGE."error.html");
			require_once(DIR_TEMP.'footer.html');
			die();
		}
	}
}
?>
