<?php
/**
*
* @version $Id: replaceAll.php 322 2009-09-14 20:19:48Z subjective $
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009 Bastian Feder, Thomas Weinert
*/
header('Content-type: text/plain');

$xml = <<<XML
<html>
<head></head>
<body>
  <div>
    <p>Hello</p>
    <p>cruel</p>
    <p>World</p>
  </div>
</body>
</html>
XML;

require_once('../FluentDOM.php');
$doc = FluentDOM($xml);
echo FluentDOM($xml)
  ->node('<b id="sample">Paragraph. </b>')
  ->replaceAll('//p');
?>