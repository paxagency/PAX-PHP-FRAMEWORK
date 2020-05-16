<?php
// SITE
define('SITE_PUBLIC', $_SERVER['HTTP_HOST'].'/');
define('SITE_ERRORS', 0);
define('SITE_SSL', 0);
define('SITE_URL_INDEX',1);
// DIRECTORIES
define('DIR_CORE',getcwd().'/app/core/');
define('DIR_PAGE',getcwd().'/app/pages/');
define('DIR_TEMP',getcwd().'/app/template/');
define('DIR_APP',getcwd().'/app/core/app/');
define('DIR_SRC',getcwd().'/src/');
// FILES
define('FILE_APP',getcwd().'/app/core/sys/app.php');
// DATABASE
define('DB_SERVER', "http://localhost:9200");
define('DB_NAME', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'password');


?>
