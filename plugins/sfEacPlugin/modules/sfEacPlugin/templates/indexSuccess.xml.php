<?php
  ob_start();
  include('indexSuccessBody.xml.php');
  echo Qubit::tidyXml(ob_get_clean());
?>
