<?php
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
}
?>
