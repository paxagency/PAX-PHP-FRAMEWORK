<?php
$path = $_URL[2];
if(!isset($path)) header('location:'.$_SERVER['HTTP_REFERER']);
$function = $_URL[3];
if(!isset($function)) header('location:'.$_SERVER['HTTP_REFERER']);
array_splice($_URL,0,4);
$_URL = array_values($_URL);
$app = new app([$path]);
$call = $app->$path->$function($_URL,$_POST);
$url = (isset($call['url'])) ? $call['url'] : $_SERVER['HTTP_REFERER'];
header('location:'.$url);
exit;
?>
