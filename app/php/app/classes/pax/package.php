<?php
class package {
    public $_folder = [];
    public $libs = '';
    public $js='';
    public $css='';
	public function __construct() {
        if(file_exists(DIR_GEN.'lib/package.json')) $this->libs = json_decode($this->stripComments(file_get_contents(DIR_GEN.'lib/package.json')),true);
    }
    public function build(){
        if($this->libs=='') return;
        foreach($this->libs['dependencies'] as $name=>$version){
            if($name!='') $this->download2($name,$this->getVersion($version));
        }
        if (!file_exists(getcwd()."/public")) mkdir(getcwd()."/public", 0777, true);
        
        $this->jsApp();
        $this->cssApp();
        $this->renderCss();
        $this->renderJs();
    }
    
    public function getVersion($v){
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
	public function render() {
		foreach($this->_folder as $name=>$folder){
		   echo "<template id='temp-{$name}'>";
           require_once($folder);
           echo "</template>";
		}
	}
	public function jsApp() {
		$this->_folder = [];
		$this->_setFolder(DIR_GEN.'js/');
		$js = '';
		
		foreach($this->_folder as $name=>$folder){
            $js.=$this->download($folder).';';
        }
		$this->js.=$js;
    }
    public function cssApp() {
		$this->_folder = [];
		$this->_setFolder(DIR_GEN.'css/');
		$css = '';
		
		foreach($this->_folder as $name=>$folder){
            $css.=$this->download($folder);
        }
		$this->css.=$css;
    }
    public function templates() {
		$this->_folder = [];
		$this->_setFolder(DIR_PAGE,[DIR_PAGE.'api']);
		
		foreach($this->_folder as $name=>$folder){
            //echo "<template id='temp-{$name}'>";
            require_once($folder);
            //echo "</template>";
        }
    }
	function formatJs($js){
		$js = $this->stripComments($js);
		$js = $this->shortenBools($js);
		//$js = $this->stripeWhitespace($js);
		//$js = $this->addColens($js);
		return $js;
    }
    function formatCss($css){
		$css = $this->stripComments($css);
		$css = $this->stripeWhitespace($css);
		return $css;
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
				$name = str_replace('.php','',$file);
				$this->_folder[$name] = $dir.$file;
			}
	  	}
	  	foreach($folders as $f) if(!in_array($f,$ignore)) $this->_setFolder($f.'/',$ignore);
	}
	protected function stripComments($content) {
        // single-line comments
        //$content = preg_replace('/\/\/.*$/m', '', $content);
        // multi-line comments
        //return preg_replace('/\/\*.*?\*\//s', '', $content);
		
		return preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', ' ', $content);
    }
	
	
    protected function shortenBools($content) {
        $content = preg_replace('/\btrue\b(?!:)/', '!0', $content);
        $content = preg_replace('/\bfalse\b(?!:)/', '!1', $content);
        // for(;;) is exactly the same as while(true)
        $content = preg_replace('/\bwhile\(!0\){/', 'for(;;){', $content);
        // now make sure we didn't turn any do ... while(true) into do ... for(;;)
        preg_match_all('/\bdo\b/', $content, $dos, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        // go backward to make sure positional offsets aren't altered when $content changes
        $dos = array_reverse($dos);
        foreach ($dos as $do) {
            $offsetDo = $do[0][1];
            // find all `while` (now `for`) following `do`: one of those must be
            // associated with the `do` and be turned back into `while`
            preg_match_all('/\bfor\(;;\)/', $content, $whiles, PREG_OFFSET_CAPTURE | PREG_SET_ORDER, $offsetDo);
            foreach ($whiles as $while) {
                $offsetWhile = $while[0][1];
                $open = substr_count($content, '{', $offsetDo, $offsetWhile - $offsetDo);
                $close = substr_count($content, '}', $offsetDo, $offsetWhile - $offsetDo);
                if ($open === $close) {
                    // only restore `while` if amount of `{` and `}` are the same;
                    // otherwise, that `for` isn't associated with this `do`
                    $content = substr_replace($content, 'while(!0)', $offsetWhile, strlen('for(;;)'));
                    break;
                }
            }
        }
        return $content;
	}
	protected function stripeWhitespace($content) {
		// uniform line endings, make them all line feed
        $content = str_replace(array("\r\n", "\r"), "\n", $content);
        // collapse all non-line feed whitespace into a single space
        $content = preg_replace('/[^\S\n]+/', ' ', $content);
        // strip leading & trailing whitespace
        $content = str_replace(array(" \n", "\n "), "\n", $content);
        // collapse consecutive line feeds into just 1
        $content = preg_replace('/\n+/', "\n", $content);
		$content =  str_replace(array("\n"), '', $content);
		return trim($content);
	}
	private function addColen2($s){
		$str = '';
		$data = explode(')',$s);
		//print_r($data);
		
		foreach($data as $n=>$d) {
			$l = substr($d,0,1);
			$del = ($n) ? ')' : '';
			//echo $l.'<br />';
			$str.=($n && 
				$l!=';' && 
				$l!=')' && 
				$l!='' && 
				$l!='}' && 
				$l!='{' &&
				$l!='?' && 
				$l!='.') ?  $del.';'.$d : $del.$d;
		}
		return $str;
	}
	private function addColens($s){
		$str = '';
		$data = explode('var ',$s);
		foreach($data as $n=>$d) {
			$l = substr($d,-1);
			$del = ($n) ? 'var ' : '';
			$str.=($l==';' ||  $l=='{' || $l=='(') ? $del.$d : $del.$d.';';
		}
		return $str;
    }
    public function download2($name,$version='1.0.0'){
        //$url = "https://github.com/paxagency/PAX-PHP-Framework/archive/1.0.1.zip";
        $expl = explode('/',$name);
        $end = end($expl);
        $url = "https://registry.npmjs.org/{$name}/-/{$end}-{$version}.tgz";

        $file = DIR_GEN."lib/{$end}.tgz"; // Local Zip File Path
        $path = DIR_GEN."lib/{$name}/{$version}/";
        
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
                $phar->extractTo(DIR_GEN.'lib/'.$name.'/',null,true); // extract all files
                rename(DIR_GEN.'lib/'.$name.'/package',$path);
                unlink($file);
            } else {
                echo "<script>alert('".$name." [".$version."] was not loaded');</script>";
                return;
            }
        }
        $package = json_decode(file_get_contents($path.'package.json'),true);
        if(isset($package['dependencies'])) {
            foreach($package['dependencies'] as $name=>$version){
                if($name!='') $this->download2($name,$this->getVersion($version));
            }
        }
        if(isset($package['main'])) $this->js.=file_get_contents($path.$package['main']);
        if(isset($package['style'])) $this->css.=file_get_contents($path.$package['style']);
	}
}
?>
