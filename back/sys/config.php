<?php
// SITE
define('SITE_PUBLIC', $_SERVER['HTTP_HOST'].'/');
define('SITE_ERRORS', 1);
define('SITE_SSL', 0);
define('SITE_WWW', 0);
define('SITE_URL_INDEX',1);
// DIRECTORIES
define('DIR_BACK',getcwd().'/back/');
define('DIR_APP',getcwd().'/back/app/');
define('DIR_SYS',getcwd().'/back/sys/');
define('DIR_DB',getcwd().'/back/db/');
define('DIR_FRONT',getcwd().'/front/');
define('DIR_PAGE',getcwd().'/front/html/pages/');
define('DIR_TEMP',getcwd().'/front/html/templates/');
// FILES
define('FILE_APP',getcwd().'/back/sys/app.php');
// DATABASE
define('DB_SERVER', "http://localhost:9200");
define('DB_PORT', "9200");
define('DB_NAME', 'dbName');

?>
