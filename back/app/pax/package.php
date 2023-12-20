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
    public $inject = ["minifier"];
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
		$this->_setFold(DIR_PAGE);
		
		foreach($this->_folder as $name=>$folder){
            $url = explode('pages/',$folder);
            $urlDash = str_replace(['.html','/'],['','-'],$url[1]);
           // echo "<script>alert('{$name}');</script>";
            echo "<template app='{$name}'>";
            require_once($folder);
            echo "</template>";
        }
    }
	function formatJs($js){
		return $js;
		return $this->app->get("minifier")::minify($js);
    }
    function formatCss($css){
		$css = $this->stripComments($css);
		$css = $this->stripeWhitespace($css);
		return $css;
	}
	public function download($url) {
        return file_get_contents($url);
    }
    private function _setFold($dir,$files='') {
   	 	$folders = [];
	  	foreach(scandir($dir) as $file) {
			if($file[0]==".") continue;
			if(is_dir($dir.$file)) {
				$this->_setFold($dir.$file.'/',$files.$file.'_');
			} else {	
				$name = $files.str_replace('.html','',$file);
				if($files!='') {
					$name = ($file=='index.html') ?   substr($files, 0, -1) : str_replace('.html','',$files.$file);
				}
				
				$this->_folder[$name] = $dir.$file;
			}
	  	}
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
	protected function stripComments($content) {
        $id = "jsdf.823.dff3sf.356";
		$content = str_replace("\//", $id, $content);
		$content =  preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', ' ', $content);
		$content = str_replace($id,"\// ", $content);
		return $content;
    }
	
	protected function stripeWhitespace($content) {
		
		// uniform line endings, make them all line feed
        $content = str_replace(array("\r\n", "\r"), "\n", $content);
        // collapse all non-line feed whitespace into a single space
        $content = preg_replace('/[^\S\n]+/', ' ', $content);
        // strip leading & trailing whitespace
        $content = str_replace(array(" \n", "\n "), " ", $content);
        // collapse consecutive line feeds into just 1
        $content = preg_replace('/\n+/', "\n", $content);
		$content =  str_replace(array("\n"), '', $content);
		return trim($content);
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
}
?>
