<h1>PHP Micro Framework</h1>
<h2>Rapid Routing & Application Design</h2>

<h3>FILE STRUCTURE</h3>
<p>The file structure is incredibly simplistic and easy to understand. Even PHP beginners and designers can create staticly routed websites within minutes.</p>

<h3>ROUTING</h3>
<p>Routing is automated by file and folder creation within </p>
<p>app > php > pages</p>

<h3>GLOBALS </h3>
<p>app > php > pax > config.php </p>
<br />
<p>PAGE_PUBLIC // Website URL</p>
<p>DIR_ROUTE // Routing Directory</p>
<p>DIR_INC // Includes Directory</p>
<p>DIR_APP // App Directory</p>
<p>$_URL // Array of GET variables</p>

<h3>APP</h3>
<p>Create new classes by file and folder creation within </p>
<p>app > php > pax > app</p>
<ul>
<li>Classes must have unique names</li>
<li>File names must be the same as their class names</li>
<li>Autoloaded classes can be set in the app > php > app > app.php file by the $_auto variable</li>
<li>Class interaction is through "Dependency Injection"</li>
<li>Dependencies can be created through <ul><li>$_inject in the app > php > app > app.php file</li><li> $_inject as a public var in the class file.</li></ul></li>
<li>Easily include the Composer Autoload to allow access to all PSR-4 PHP libraries needed</li>
</ul>
</ul>

<h3>INIT</h3>
<p>Instantiate classes from any routed or included page. </p>
<p>app > php > pages </p>
<p>app > php > includes</p>
<br />
<pre>
require_once(DIR_APP.'app.php');<br />
$app = new app(['new_class']);<br />
$app->new_class->hello();
</pre>
