<h1>PAX PHP Micro Framework</h1>
<h2>Rapid Routing & Application Design</h2>
<a href='http://docs.paxagency.com/php'>http://docs.paxagency.com/php</a>
<br />
<h2>FILE STRUCTURE</h2>
<p>The file structure is incredibly simplistic and easy to understand. Even PHP beginners and designers can create staticly routed websites within minutes.</p>

<ul class='files'>
    <li><span class='fi-page'></span> index.php</li>
    <li><span class='fi-folder'></span> app</li>
      <ul><li class='m1'><span class='fi-folder'></span> css</li>
      <li class='m1'><span class='fi-folder'></span> js</li>
      <li class='m1'><span class='fi-folder'></span> php</li>
      <ul><li class='m2'><span class='fi-folder'></span> pages</li>
      <li class='m2'><span class='fi-folder'></span> includes</li>
      <li class='m2'><span class='fi-folder'></span> app</li></ul></ul>
  </ul>
  <br />
<h2>ROUTING</h2>
<p>Routing is automated by file and folder creation within </p>
<p><strong>app > php > pages</strong></p>
<p>***********************</p>
<p><em>If you initially get an error</em></p>
<ul>
<li><em>Check the DIR_INDEX so it reflects the proper sub-folder index from the domain</em></li>
<li><em>Make sure you rename the htaccess to ".htaccess"</em></li>
</ul>
<p>***********************</p>
<br />
<h2>GLOBALS </h2>
<p><strong>app > php > pax > config.php </strong></p>
<br />
<p><strong>PAGE_PUBLIC</strong> <em>(Website URL)</em></p>
<p><strong>DIR_ROUTE</strong> <em>(Routing Directory)</em></p>
<p><strong>DIR_INC</strong> <em>(Includes Directory)</em></p>
<p><strong>DIR_APP</strong> <em>(App Directory)</em></p>
<p><strong>DIR_CLASS</strong> <em>(Class Directory)</em></p>
<p><strong>DIR_INDEX</strong> <em>(Root Folder Position from Domain)</em></p>
<p><strong>$_URL</strong> <em>(Array of URL variables separated by "/")</em></p>
<br />
<h2>APP</h2>
<p>Create new classes by file and folder creation within </p>
<p><strong>app > php > app > classes</strong></p>
<ul>
<li>Classes must have unique names</li>
<li>File names must be the same as their class names</li>
<li>Autoloaded classes can be set in the <strong>app > php > app > app.php</strong> file by the $_auto variable</li>
<li>Class interaction is through "Dependency Injection"</li>
<li>Dependencies can be created through <ul>
  <li>$_inject in the <strong>app > php > app > app.php</strong> file <br /><em>(Globally will inject every new class)</em></li>
  <li>$_inject as a public var in the class file.</li></ul></li>
<li>Easily include the Composer Autoload to allow access to all PSR-4 PHP libraries needed</li>
</ul>
</ul>
<br />
<h2>INIT</h2>
<p>Instantiate classes from any routed or included page. </p>
<p><strong>app > php > pages </strong></p>
<p><strong>app > php > includes</strong></p>
<br />
<pre>
require_once(DIR_APP);<br />
$app = new app(['new_class']);<br />
$app->new_class->hello();
</pre>
