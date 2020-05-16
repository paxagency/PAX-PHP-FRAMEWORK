<pre>
<?php
$path = $_URL[2];
if(!isset($path)) {echo '{"error":"Please add a class to load in URL"}'; exit;}
$function = $_URL[3];
if(!isset($function)) {echo '{"error":"Please add a function to call in URL"}'; exit;}
array_splice($_URL,0,4);
$_URL = array_values($_URL);
$app->load([$path]);
echo json_encode($app->$path->$function($_URL,$_POST),JSON_PRETTY_PRINT);
exit;
?>
</pre>
