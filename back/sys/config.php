<?php
$_SSL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 1 : 0;
$_PROTOCOL =  ($_SSL) ? "https://" : "http://";
// SITE
define('SITE_PUBLIC', $_PROTOCOL.$_SERVER['HTTP_HOST'].'/');
define('SITE_SSL', $_SSL);
define('SITE_ERRORS', 1);
define('SITE_PROD', 0);
// DIRECTORIES
define('DIR_BACK',$_DIR.'back/');
define('DIR_APP',$_DIR.'back/app/');
define('DIR_SYS',$_DIR.'back/sys/');
define('DIR_DB',$_DIR.'back/db/');
define('DIR_FRONT',$_DIR.'front/');
define('DIR_PAGE',$_DIR.'front/html/pages/');
define('DIR_TEMP',$_DIR.'front/html/templates/');
// FILES
define('FILE_APP',$_DIR.'back/sys/app.php');
// DATABASE
define('DB_CLASS', "seed");
define('DB_SERVER', "localhost");
define('DB_PORT', "9200");
define('DB_NAME', 'dbName');
define('DB_USER', 'root');
define('DB_PASS', 'root');
?>
