<style>
.files {width:100%; margin:0;padding:0;
    border:solid thin #eee;
  }
.files li {
  list-style-type:none;
font-size:25px;
line-height: 1.8em;
padding-left:50px;

}
.files li:nth-child(even) {
  background:#f5f5f5;
}
.files li.m1 {padding-left:100px;}
.files li.m2 {padding-left:150px;}
.files li span {opacity:.4;}

.m1 {padding-left:50px;}
.m2 {padding-left:100px;}
pre p {text-align:left; margin:0;padding:0;  line-height: .5em;}
.code p {text-align:left; margin:0 !important; }
.code {background:#fafafa; border:solid thin #ccc; padding:20px; font-family:monospace; margin:20px 0;}
hr {padding:30px; background:#ddd !important; margin:0; }
@media all and (max-width: 735px) {
.files li {font-size:1em;}
.files li {padding-left:10px}
.files li.m1 {padding-left:30px;}
.files li.m2 {padding-left:60px;}
.m1 {padding-left:30px;}
.m2 {padding-left:60px;}
}
  </style>
<h1>PHP Micro Framework</h1>
<h2>Rapid Routing & Application Design</h2>

<h3>FILE STRUCTURE</h3>
<p>The file structure is incredibly simplistic and easy to understand. Even PHP beginners and designers can create staticly routed websites within minutes.</p>

<ul class='files' >
    <li><span class='fi-page'></span> index.php</li>
    <li><span class='fi-folder'></span> app</li>
      <li class='m1'><span class='fi-folder'></span> css</li>
      <li class='m1'><span class='fi-folder'></span> js</li>
      <li class='m1'><span class='fi-folder'></span> php</li>
      <li class='m2'><span class='fi-folder'></span> pages</li>
      <li class='m2'><span class='fi-folder'></span> includes</li>
      <li class='m2'><span class='fi-folder'></span> pax</li>
  </ul>
  
<h3>ROUTING</h3>
<p>Routing is automated by file and folder creation within </p>
<p><strong>app > php > pages</strong></p>

<h3>GLOBALS </h3>
<p><strong>app > php > pax > config.php </strong></p>
<br />
<p>PAGE_PUBLIC // Website URL</p>
<p>DIR_ROUTE // Routing Directory</p>
<p>DIR_INC // Includes Directory</p>
<p>DIR_APP // App Directory</p>
<p>$_URL // Array of GET variables</p>

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
