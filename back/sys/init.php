<?php
/*********************
docs.paxagency.com/php
*********************/
require_once($_DIR.'back/sys/config.php');
(SITE_ERRORS) ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
if((SITE_SSL && !isset($_SERVER['HTTPS'])) || (SITE_SSL && $_SERVER['HTTP_X_FORWARDED_PROTO']!='https')) header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

$_URL=$_URL_VARS=array_slice(explode('/',parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)),1);
$_PATH='error.html';
$path='';
foreach($_URL as $n=>$url) {
	array_shift($_URL_VARS);
	$path.=($n && $url!='')?'/'.strtolower($url):strtolower($url);
	if(file_exists(DIR_PAGE.$path.'/index.html')) $_PATH = $path.'/index.html';
	if(file_exists(DIR_PAGE.$path.'.html')) {$_PATH=$path.'.html';break;}
    
}
require_once(DIR_SYS.'template.php');
?>