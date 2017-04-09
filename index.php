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

PAX PHP Framework 1.0
Copyright 2017 PAX Agency
Created by Albert Kiteck
www.paxagency.com/php
****************************************
****************************************/

require_once('app/php/pax/config.php');
$_URL=array_slice(array_values(array_filter(explode('/',htmlspecialchars($_SERVER['REQUEST_URI'])))),DIR_INDEX);
$_PATH='error';

foreach($_URL as $n=>$url) {
	$path.=($n) ? '/'.$url : $url;
	if($_PATH=='error' && file_exists(DIR_ROUTE.$path.'.php')) $_PATH=$path; 
}

if($_PATH=='error' && file_exists(DIR_ROUTE.$path.'/index.php')) $_PATH = $path.'/index';
if($_URL[0]!='call') require_once(DIR_INC.'header.php');
require_once(DIR_ROUTE.$_PATH.'.php');
if($_URL[0]!='call') require_once(DIR_INC.'footer.php');


?>