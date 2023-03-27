<?php
/*****************************
docs.paxagency.com/php/routing 
*****************************/
require_once(FILE_APP);
$app = new app();
$_CONFIG = $app->get("control")->verifyUser();

if($_URL[0]=='test') return require_once(DIR_PAGE.$_PATH);
//ALLOW API FOR LOGGED & NON LOGGED AUTH LOGIN
if(($_CONFIG["logged"] && $_URL[0]=='api') 
	|| ($_URL[0]=='api' && $_URL[1]=='auth')) return require_once(DIR_SYS.'api.php');
if($_CONFIG["logged"] && $_URL[0]=='account') header("location:/");
//IF NOT LOGGED IN SEND TO ACCOUNT
if(!$_CONFIG["logged"]){
	$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	if($_URL[0]!='account' && $ajax) {echo '{"redirect":"/account","error":1,"message":"Not logged in"}'; die();}
	if($_URL[0]!='account') header("location:/account/");
	if($_URL[0]=='account') return require_once(DIR_PAGE.$_PATH);
}


require_once(DIR_TEMP.'header.html');
	 if(SITE_BUILD) $app->get('package')->build();
	 if(SITE_TEMP) $app->get('package')->templates();
     require_once(DIR_PAGE.$_PATH);
     echo "<script>var config=".json_encode($_CONFIG).";</script>";
require_once(DIR_TEMP.'footer.html');


?>