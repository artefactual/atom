<?php
/**
*
* @version $Id: filter-fn.php 429 2010-03-29 08:05:32Z subjective $
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009 Bastian Feder, Thomas Weinert
*/
header('Content-type: text/plain');

$xml = <<<XML
<html>
<head></head>
<body>
  <div id="first"></div>
  <div id="second"></div>
  <div id="third"></div>
  <div id="fourth"></div>
  <div id="fifth"></div>
  <div id="sixth"></div>
</body>
</html>
XML;

require_once('../FluentDOM.php');
echo FluentDOM($xml)
  ->find('//div')
  ->attr('border', 1)
  ->filter(
    function($node, $index) {
      if ($index == 1 ||
          FluentDOM($node)->attr('id') == 'fourth') {
        return TRUE;
      }
      return FALSE;
    }
  )
  ->attr('style', 'text-align: center;');
?>