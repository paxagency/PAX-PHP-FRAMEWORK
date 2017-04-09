# PAX-PHP-FRAMEWORK
PHP Micro Framework
Rapid Routing & Application Design

FILE STRUCTURE
The file structure is incredibly simplistic and easy to understand. Even PHP beginners and designers can create staticly routed websites within minutes.

ROUTING
Routing is automated by file and folder creation within 
app > php > pages

GLOBALS
app > php > pax > config.php

PAGE_PUBLIC // Website URL
DIR_ROUTE // Routing Directory
DIR_INC // Includes Directory
DIR_APP // App Directory
$_URL // Array of GET variables

APP
Create new classes by file and folder creation within 
app > php > pax > app

INIT
Instantiate classes from any routed or included page. 
app > php > pages 
app > php > includes

require_once(DIR_APP.'app.php');
$app = new app(['new_class']);
$app->new_class->hello();
