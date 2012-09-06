<?php
/**
* Sample how to use a custom FluentDOMLoader
*
* @version $Id: iniToXml.php 429 2010-03-29 08:05:32Z subjective $
* @package FluentDOM
* @subpackage examples
*/

require_once(dirname(__FILE__).'/../../FluentDOM.php');
require_once(dirname(__FILE__).'/FluentDOMIniLoader.php');

$iniFile = dirname(__FILE__).'/sample.ini';

$fd = new FluentDOM();
$fd->setLoaders(
  array(
    new FluentDOMIniLoader()
  )
);

header('Content-type: text/plain');
echo $fd->load($iniFile, 'text/ini')->formatOutput();

echo "\n\n";
echo 'URL: ', $fd->find('//URL')->text();

?>