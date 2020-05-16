<?php
/*********************
docs.paxagency.com/php
*********************/
require_once('app/core/sys/config.php');
if(SITE_ERRORS) ini_set('display_errors', 1);
if(SITE_SSL) {if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTP_X_FORWARDED_PROTO']!='https') header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);}

$_URL=array_slice(explode('/',parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)),SITE_URL_INDEX);
$_PATH='error.php';
$_PAGE = 'error';
$path='';
foreach($_URL as $n=>$url) {
	$path.=($n)?'/'.$url:$url;
	if(file_exists(DIR_PAGE.$path.'.php')) {$_PATH=$path.'.php';$_PAGE=$url;break;}
}
if($_PATH=='error.php' && file_exists(DIR_PAGE.$path.'/index.php')) {$_PATH = $path.'/index.php'; $_PAGE='index';}

require_once(DIR_CORE.'sys/template.php');
?>
