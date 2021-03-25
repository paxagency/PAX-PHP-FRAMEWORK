<?php
/*********************
docs.paxagency.com/php/application/api
*********************/
$pretty = (isset($_GET["pretty"]) && $_GET["pretty"]) ? 1 : 0;
$post = (isset($_GET["post"]) && $_GET["post"]) ? 1 : 0;

if($pretty) echo "<pre>";
$path = $_URL[1];
if(!isset($path)) {echo '{"error":"Please add a class to load in URL"}'; exit;}
$function = $_URL[2];
if(!isset($function)) {echo '{"error":"Please add a function to call in URL"}'; exit;}
array_splice($_URL,0,3);
$_URL = array_values($_URL);

if($post) {
	$call = $app->get($path)->$function($_URL,$_POST);
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
