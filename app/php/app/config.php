<?php
// SITE
define('SITE_PUBLIC', $_SERVER['HTTP_HOST']);
define('SITE_ERRORS', 1);
define('SITE_SSL', 0);
// DIRECTORIES
define('DIR_ROUTE',getcwd().'/app/php/pages/');
define('DIR_INC',getcwd().'/app/php/includes/');
define('DIR_APP',getcwd().'/app/php/app/app.php');
define('DIR_CLASS',getcwd().'/app/php/app/classes/');
define('DIR_INDEX',1);
// DATABASE
define('DB_SERVER', "http://localhost:9200");
define('DB_NAME', 'database');
define('DB_USER', 'username');
define('DB_PASS', 'password');
?>
