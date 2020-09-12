<?php
/*****************************
docs.paxagency.com/php/routing 
*****************************/
require_once(FILE_APP);
$app = new app(['session','package','crud']);

if($_URL[0]=='api') return require_once(DIR_SYS.'api/'.$_URL[1].'.php');
require_once(DIR_TEMP.'header.html');
if(JS_PACKAGE) $app->package->build();
(HTML_LOAD_ALL) ? $app->package->templates() : require_once(DIR_PAGE.$_PATH);
require_once(DIR_TEMP.'footer.html');
?>