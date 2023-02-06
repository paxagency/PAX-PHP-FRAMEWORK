<?php
/****************************************
docs.paxagency.com/php/libraries/package
*****************************************/
class package {
    public $_folder = [];
    public $libs = '';
    public $js='';
    public $css='';
    public $app;
    public $inject = ["jshrink"];
	public function __construct() {
        if(file_exists(DIR_FRONT.'lib/package.json')) 
            $this->libs = json_decode($this->stripComments(file_get_contents(DIR_FRONT.'lib/package.json')),true);
    }
    public function build(){
        if($this->libs=='') $this->libs = ['dependencies'=>[]];
        if (!file_exists(DIR_FRONT."lib")) mkdir(DIR_FRONT."lib", 0777, true);
        foreach($this->libs['dependencies'] as $name=>$version){
            if($name!='') $this->downloadCurl($name,$this->getVersion($version));
        }
        if (!file_exists(getcwd()."/public")) mkdir(getcwd()."/public", 0777, true);
        
        $this->jsApp();
        $this->cssApp();
        $this->renderCss();
        $this->renderJs();
    }
    
    public function getVersion($v){
        return str_replace('^','',$v);
        return preg_replace("/[^0-9.]/", "",$v);
    }
    public function renderJs(){
        $file = fopen(getcwd()."/public/app.js","w");
        fwrite($file, $this->formatJs($this->js));
        fclose($file);
    }
    public function renderCss(){
        $file = fopen(getcwd()."/public/app.css","w");
        fwrite($file, $this->formatCss($this->css));
        fclose($file);
    }
	public function jsApp() {
		$this->_folder = [];
		$this->_setFolder(DIR_FRONT.'js/');
		$js = '';
		foreach($this->_folder as $name=>$folder){
            $js.=$this->download($folder).';';
        }
		$this->js.=$js;
    }
    public function cssApp() {
		$this->_folder = [];
		$this->_setFolder(DIR_FRONT.'css/');
		$css = '';
		foreach($this->_folder as $name=>$folder){
            $css.=$this->download($folder);
        }
		$this->css.=$css;
    }
    public function templates() {
		$this->_folder = [];
		$this->_setFolderTemp(DIR_PAGE);
     
		foreach($this->_folder as $name=>$folder){
            $url = explode('pages/',$folder);
            $urlDash = str_replace(['.html','/'],['','-'],$url[1]);
            echo "<template app='{$name}'>";
            require_once($folder);
            echo "</template>";
        }
    }
	function formatJs($js){
		return $this->app->get("jshrink")::minify($js);
    }
    function formatCss($css){
    	return $this->minify_css($css);
	}
	public function download($url) {
        return file_get_contents($url);
    }
	private function _setFolder($dir,$ignore=[]) {
		$folders = [];
	  	foreach(scandir($dir) as $file) {
			if($file[0]==".") continue;
			if(is_dir($dir.$file)) {
				$folders[] = $dir.$file;
			} else {
				$name = str_replace('.html','',$file);
				$this->_folder[$name] = $dir.$file;
			}
	  	}
	  	foreach($folders as $f) if(!in_array($f,$ignore)) $this->_setFolder($f.'/',$ignore);
	}
	private function _setFolderTemp($dir,$sub=false) {
		$folders = [];
	  	foreach(scandir($dir) as $file) {
			if($file[0]==".") continue;
			if(is_dir($dir.$file)) {
				$folders[] = $dir.$file;
				$subst= ($sub) ? $sub.$file.'-' : $file.'-';
			} else {
				$name = str_replace('.html','',$file);
				if($sub) {
					$name = ($file=='index.html') ?   substr($sub, 0, -1) : str_replace('.html','',$sub.$file);
				}
				$this->_folder[$name] = $dir.$file;
			}
	  	}
	  	foreach($folders as $f) $this->_setFolderTemp($f.'/',$subst);
	}
	
    public function downloadCurl($name,$version='1.0.0'){
        //$url = "https://github.com/paxagency/PAX-PHP-Framework/archive/1.0.1.zip";
        $expl = explode('/',$name);
        $end = end($expl);
        $url = "https://registry.npmjs.org/{$name}/-/{$end}-{$version}.tgz";
        $file = DIR_FRONT."lib/{$end}.tgz"; // Local Zip File Path
        $path = DIR_FRONT."lib/{$name}/{$version}/";
        
        if(!file_exists($path)) {
            $resource = fopen($file, "w");
            //DOWNLOAD FILE
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
            curl_setopt($ch, CURLOPT_FILE, $resource);
            $data = curl_exec($ch);
           
            curl_close($ch);
            if($data) {
                $phar = new PharData($file);
                $phar->extractTo(DIR_FRONT.'lib/'.$name.'/',null,true); // extract all files
                rename(DIR_FRONT.'lib/'.$name.'/package',$path);
                unlink($file);
            } else {
                echo "<script>alert('".$name." [".$version."] was not loaded');</script>";
                return;
            }
        }
        $package = json_decode(file_get_contents($path.'package.json'),true);
        if(isset($package['dependencies'])) {
            foreach($package['dependencies'] as $name=>$version){
                if($name!='') $this->downloadCurl($name,$this->getVersion($version));
            }
        }
        if(isset($package['main'])) $this->js.=file_get_contents($path.$package['main']);
        if(isset($package['style'])) $this->css.=file_get_contents($path.$package['style']);
	}
	
	function minify_css($input) {
		if(trim($input) === "") return $input;
		return preg_replace(
			array(
				// Remove comment(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				// Remove unused white-space(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
				// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
				'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
				// Replace `:0 0 0 0` with `:0`
				'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
				// Replace `background-position:0` with `background-position:0 0`
				'#(background-position):0(?=[;\}])#si',
				// Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
				'#(?<=[\s:,\-])0+\.(\d+)#s',
				// Minify string value
				'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
				'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
				// Minify HEX color code
				'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
				// Replace `(border|outline):none` with `(border|outline):0`
				'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
				// Remove empty selector(s)
				'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
			),
			array(
				'$1',
				'$1$2$3$4$5$6$7',
				'$1',
				':0',
				'$1:0 0',
				'.$1',
				'$1$3',
				'$1$2$4$5',
				'$1$2$3',
				'$1:0',
				'$1$2'
			),
		$input);
	}
}
?>
