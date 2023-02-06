<?php
/*****************************
docs.paxagency.com/php/routing 
*****************************/
require_once(FILE_APP);
$app = new app();

if($_URL[0]=='api') return require_once(DIR_SYS.'api.php');
require_once(DIR_TEMP.'header.html');
	 if(SITE_BUILD) $app->get('package')->build();
	 if(SITE_TEMP) $app->get('package')->templates();
     require_once(DIR_PAGE.$_PATH);
require_once(DIR_TEMP.'footer.html');
?>