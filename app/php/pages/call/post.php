<?php
require_once(DIR_APP.'app.php');
foreach($_POST as $key=>$post) $_POST[$key] = filter_var($post, FILTER_SANITIZE_STRING);

$path = $_URL[2];
$function = $_URL[3];
array_splice($_URL,0,4);
$_URL = array_values($_URL);
$app = new app([$path]);
$call = $app->$path->$function($_POST,$_URL);
$url = ($call) ? $call : $_SERVER['HTTP_REFERER'];
header('location:'.$url);

?>
