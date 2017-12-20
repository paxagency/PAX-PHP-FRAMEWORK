<?php
class app {
	public $_auto = ['session'];
	public $_inject = ['session'];
	public $_folder = [];
	public function __construct($classes=[],$inject=[]) {
		$this->_setFolder(DIR_CLASS);
		foreach($this->_auto as $a) $this->_init($a);
		if($classes) $this->load($classes,$inject);
	}
	public function load($classes=[],$inject=[]) {
		$inject = ($inject) ? $inject : $this->_inject;
		foreach($classes as $c) $this->_init($c,$inject);
	}
	public function _init($name,$inject=[]){
		if(!isset($this->_folder[$name])) return;
	  	require_once($this->_folder[$name]);
		$this->$name = new $name();
		if(isset($this->$name->_inject)) $inject=array_merge($inject,$this->$name->_inject);
		foreach($inject as $p) {
			if(!isset($this->$p)) $this->_init($p,$this->_inject);
			$this->$name->$p = $this->$p;
		}
	}
	public function _setFolder($dir) {
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
}
?>
