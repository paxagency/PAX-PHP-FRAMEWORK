<?php
/*****************************
docs.paxagency.com/php/routing 
*****************************/
require_once(FILE_APP);
$app = new app(['session','package']);
if($_URL[0]=='api') return require_once(DIR_PAGE.$_PATH);
require_once(DIR_TEMP.'header.php');
if(APP_PACKAGE) $app->package->build();
(ROUTE_MODE=='JS') ? $app->package->templates() : require_once(DIR_PAGE.$_PATH);
require_once(DIR_TEMP.'footer.php');
?>