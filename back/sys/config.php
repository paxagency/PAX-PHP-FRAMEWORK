<?php
// SITE
define('SITE_PUBLIC', $_SERVER['HTTP_HOST'].'/');
define('SITE_URL_INDEX',1);
define('SITE_ERRORS', 1);
define('SITE_SSL', 0);
define('SITE_WWW', 0);
//PACKAGE
define('SITE_BUILD',0);
define('SITE_TEMP',0);
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
define('DB_CLASS', "seed");
define('DB_SERVER', "localhost");
define('DB_PORT', "9200");
define('DB_NAME', 'dbName');
define('DB_USER', 'root');
define('DB_PASS', 'root');
//S3
define('S3_KEY','{ACCESSKEY}');
define('S3_SECRET','{SECRETKEY}');
define('S3_BUCKET','bucket');
define('S3_URL','https://{key}.s3.amazonaws.com');
//EMAIL
define('EM_KEY', '{ACCESSKEY}');
define('EM_CLASS','postmark');
define('EM_FROM','info@website.com');
define('EM_SITE',$_SERVER['HTTP_HOST']);
?>
