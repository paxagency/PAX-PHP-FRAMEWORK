<?php
// SITE
define('SITE_PUBLIC', $_SERVER['HTTP_HOST']);
define('SITE_ERRORS', 1);
define('SITE_SSL', 0);
define('SITE_URL_INDEX',1);
// DIRECTORIES
define('DIR_GEN',getcwd().'/app/');
define('DIR_APP',getcwd().'/app/php/app/');
define('DIR_PAGE',getcwd().'/app/php/pages/');
define('DIR_TEMP',getcwd().'/app/php/template/');
// FILES
define('FILE_APP',getcwd().'/app/php/app/core/app.php');
// DATABASE
define('DB_SERVER', $_SERVER['HTTP_HOST']);
define('DB_PORT', "9200");
define('DB_NAME', 'dbname');
?>
