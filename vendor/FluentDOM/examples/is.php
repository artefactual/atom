<?php
/**
*
* @version $Id: is.php 322 2009-09-14 20:19:48Z subjective $
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009 Bastian Feder, Thomas Weinert
*/
header('Content-type: text/plain');

$xml = <<<XML
<html>
<head></head>
<body>
  <form><input type="checkbox" /></form>
  <div></div>
</body>
</html>
XML;

require_once('../FluentDOM.php');
$dom = FluentDOM($xml);
$isFormParent = $dom
  ->find('//input[@type = "checkbox"]')
  ->parent()
  ->is('name() = "form"');
$dom
  ->find('//div')
  ->text('$isFormParent = '.($isFormParent ? 'TRUE' : 'FALSE'));

echo $dom;
?>