<?php
$errors = [];
$referer  = ($_SERVER['HTTP_REFERER']=='') ? 0 : $_SERVER['HTTP_REFERER'];
$path = $_URL[2];
$function = $_URL[3];

if(!isset($path)) $errors[] = ['error'=>1,'message'=>'Must include class name in url path.'];
if(!isset($function))  $errors[] = ['error'=>1,'message'=>'Must include method name in url path.'];
if(!isset($app->_folder[$path])) $errors[] = ['error'=>1,'message'=>'This class does not exist.'];
if($errors && !$referer) {
    echo '<pre>'.json_encode($errors,JSON_PRETTY_PRINT).'</pre>';
    return;
}
if($errors) header('location:'.$referer);

array_splice($_URL,0,4);
$_URL = array_values($_URL);
$call = $app->get($path)->$function($_URL,$_POST);
$url = (isset($call['url'])) ? $call['url'] : $referer;
if($url) {
    header('location:'.$url); 
} else {
    echo '<pre>'.json_encode($call,JSON_PRETTY_PRINT).'</pre>';
}
exit;
?>
