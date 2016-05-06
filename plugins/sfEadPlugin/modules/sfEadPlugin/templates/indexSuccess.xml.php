<?php
  ob_start();
  include('indexSuccessHeader.xml.php');
  include('indexSuccessBody.xml.php');
  echo Qubit::tidyXml(ob_get_clean());
?>
