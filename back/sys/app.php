<?php
class app {
	public $_autoload = ['session'];
	public $_folder = [];
	public function __construct($classes=[],$autoload=[]) {
		$this->_setFolder(DIR_APP);
		$this->_autoload=array_merge($autoload,$this->_autoload);
		$this->load($this->_autoload,[],false);
		$this->load($classes);
	}
	public function load($classes=[],$inject=[],$auto=true) {
		if($classes) foreach($classes as $class) $this->_init($class,$inject,$auto);
	}
	public function _init($class,$inject=[],$auto=true){
		if(!isset($this->_folder[$class]) || isset($this->$class)) return;
	  	require_once($this->_folder[$class]);
		$this->$class = new $class();
		if($auto) $inject=array_merge($this->_autoload,$inject);
		if(isset($this->$class->_inject)) $inject=array_merge($this->$class->_inject,$inject);
		$this->_inject($class,$inject);
	}
	public function _inject($class,$inject){
		foreach($inject as $inj) {
			$this->_init($inj);
			$this->$class->$inj = $this->$inj;
		}
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
}
?>
