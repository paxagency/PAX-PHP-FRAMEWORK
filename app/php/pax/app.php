<?php
class app {
	public $_auto = ['session'];
	public $_inject = ['session'];
	public $_folder = [];
	public function __construct($classes,$inject=[]) {
		$this->setFolder(DIR_APP.'app');
		foreach($this->_auto as $a) $this->init($a);
		if($classes) $this->load($classes,$inject);
	}
	public function load($classes,$inject=[]) {
		$inject = ($inject) ? $inject : $this->_inject;
		foreach($classes as $c) $this->init($c,$inject);
	}
	public function init($name,$inject=[]){
	  	require_once($this->_folder[$name]);
		$this->$name = new $name();
		if($this->$name->_inject) $inject=array_merge($inject,$this->$name->_inject);
		foreach($inject as $p) {
			if(!$this->$p) $this->init($p,$this->_inject);
			$this->$name->$p = $this->$p;
		}
	}
	public function setFolder($dir) {
	  foreach(scandir($dir) as $file) {
	    if($file[0]==".") continue;
	    if(is_dir($dir.'/'.$file)) {
	        $folders[] = $dir.'/'.$file;
	    } else {
		$name = str_replace('.php','',$file);
	     	$this->_folder[$name] = $dir.'/'.$file;
	    }
	  }
	  foreach($folders as $f) $this->setFolder($f);
	}
}
?>
