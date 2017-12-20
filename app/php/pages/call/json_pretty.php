<pre>
<?php
require_once(DIR_APP);
$path = $_URL[2];
$function = $_URL[3];
array_splice($_URL,0,4);
$_URL = array_values($_URL);
$app = new app([$path]);
echo json_encode($app->$path->$function($_POST,$_URL),JSON_PRETTY_PRINT);
exit;
?>
</pre>