<h1>PHP Micro Framework</h1>
<h2>Rapid Routing & Application Design</h2>
<br />
<h3>FILE STRUCTURE</h3>
<p>The file structure is incredibly simplistic and easy to understand. Even PHP beginners and designers can create staticly routed websites within minutes.</p>

<ul class='files' >
    <li><span class='fi-page'></span> index.php</li>
    <li><span class='fi-folder'></span> app</li>
      <ul><li class='m1'><span class='fi-folder'></span> css</li>
      <li class='m1'><span class='fi-folder'></span> js</li>
      <li class='m1'><span class='fi-folder'></span> php</li>
      <ul><li class='m2'><span class='fi-folder'></span> pages</li>
      <li class='m2'><span class='fi-folder'></span> includes</li>
      <li class='m2'><span class='fi-folder'></span> pax</li></ul></ul>
  </ul>
  <br />
<h3>ROUTING</h3>
<p>Routing is automated by file and folder creation within </p>
<p><strong>app > php > pages</strong></p>
<br />
<h3>GLOBALS </h3>
<p><strong>app > php > pax > config.php </strong></p>
<br />
<p>PAGE_PUBLIC // Website URL</p>
<p>DIR_ROUTE // Routing Directory</p>
<p>DIR_INC // Includes Directory</p>
<p>DIR_APP // App Directory</p>
<p>$_URL // Array of GET variables</p>
<br />
<h3>APP</h3>
<p>Create new classes by file and folder creation within </p>
<p><strong>app > php > pax > app</strong></p>
<ul>
<li>Classes must have unique names</li>
<li>File names must be the same as their class names</li>
<li>Autoloaded classes can be set in the <strong>app > php > pax > app.php</strong> file by the $_auto variable</li>
<li>Class interaction is through "Dependency Injection"</li>
<li>Dependencies can be created through <ul>
  <li>$_inject in the <strong>app > php > pax > app.php</strong> file <br /><em>(Globally will inject every new class)</em></li>
  <li>$_inject as a public var in the class file.</li></ul></li>
<li>Easily include the Composer Autoload to allow access to all PSR-4 PHP libraries needed</li>
</ul>
</ul>
<br />
<h3>INIT</h3>
<p>Instantiate classes from any routed or included page. </p>
<p><strong>app > php > pages </strong></p>
<p><strong>app > php > includes</strong></p>
<br />
<pre>
require_once(DIR_APP.'app.php');<br />
$app = new app(['new_class']);<br />
$app->new_class->hello();
</pre>
