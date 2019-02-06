<?php
/*****************************
docs.paxagency.com/php/routing 
*****************************/
require_once(FILE_APP);
$app = new app(['session']);
if($_URL[0]=='api') return require_once(DIR_PAGE.$_PATH);
require_once(DIR_TEMP.'includes/header.php');
require_once(DIR_PAGE.$_PATH);
require_once(DIR_TEMP.'includes/footer.php');
?>