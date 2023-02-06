<?php
/*********************
docs.paxagency.com/php
*********************/
require_once('back/sys/config.php');
(SITE_ERRORS) ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
if(SITE_SSL) {
    if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTP_X_FORWARDED_PROTO']!='https') header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}
if(SITE_WWW && strpos($_SERVER['HTTP_HOST'],"www.")===false)  
    header('Location: '.$_SERVER['REQUEST_SCHEME'].'://www.'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

$_URL=array_slice(explode('/',parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)),SITE_URL_INDEX);
$_PATH='error.html';
$_PAGE = 'error';
$path='';
foreach($_URL as $n=>$url) {
    $path.=($n && $url!='')?'/'.$url:$url;
    if(file_exists(DIR_PAGE.$path.'/index.html')) {$_PATH = $path.'/index.html';$_PAGE='index';}
    if(file_exists(DIR_PAGE.$path.'.html')) {$_PATH=$path.'.html';$_PAGE=$url;break;}
}
require_once(DIR_SYS.'template.php');
?>
