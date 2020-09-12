<?php
class app {
    public $_folder = [];
	public function __construct($classes=[]) {
        $this->_setFolder(DIR_APP);
		$this->load($classes);
    }
	public function load($classes=[]) {
        if($classes) foreach($classes as $class) $this->_init($class);
	}
	public function _init($class){
        if(!isset($this->_folder[$class]) || isset($this->$class)) return;
        require_once($this->_folder[$class]);
        $this->$class = new $class();
        if(!isset($this->$class->_ignore)) $this->$class->app = $this;
        if(isset($this->$class->_inject)) foreach($this->$class->_inject as $cl) $this->_init($cl);
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
