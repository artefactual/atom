<?php
/**
*
* @version $Id: parents.php 322 2009-09-14 20:19:48Z subjective $
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009 Bastian Feder, Thomas Weinert
*/
header('Content-type: text/plain');

$xml = <<<XML
<html>
<head></head>
<body>
  <div>
    <p>
      <span>
        <b>My parents are: </b>
      </span>
    </p>
  </div>
</body>
</html>
XML;

require_once('../FluentDOM.php');
$dom = FluentDOM($xml);
$parents = implode(
  ', ',
  $dom
    ->find('//b')
    ->parents()
    ->map(
        create_function('$node', 'return $node->tagName;')
      )
);
echo $dom
  ->find('//b')
  ->append('<strong>'.htmlspecialchars($parents).'</strong>');
?>