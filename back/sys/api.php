<?php
/*********************
docs.paxagency.com/php/application/api
*********************/
$pretty = (isset($_GET["pretty"]) && $_GET["pretty"]) ? 1 : 0;
$post = (isset($_GET["post"]) && $_GET["post"]) ? 1 : 0;

if($pretty) echo "<pre>";
if(!isset($_URL[1])) {echo '{"error":"Please add a class to load in URL"}'; exit;}
$path = $_URL[1];
if(!isset($_URL[2])) {echo '{"error":"Please add a function to call in URL"}'; exit;}
$function = $_URL[2];
array_splice($_URL,0,3);
$_URL = array_values($_URL);

if($post) {
	$class = $app->get($path);
	if(!method_exists($class, $function)) {echo '{"error":"Class or method does not exist"}'; exit;}
	$call = $class->$function($_URL,$_POST);
	$referer  = ($_SERVER['HTTP_REFERER']=='') ? SITE_PUBLIC : $_SERVER['HTTP_REFERER'];
	$url = (isset($call['url'])) ? $call['url'] : $referer;
    header('location:'.$url); 
    exit;
}

echo ($pretty) 
	? json_encode($app->get($path)->$function($_URL,$_POST),JSON_PRETTY_PRINT)
	: json_encode($app->get($path)->$function($_URL,$_POST));

if($pretty) echo "</pre>";
exit;
?>
