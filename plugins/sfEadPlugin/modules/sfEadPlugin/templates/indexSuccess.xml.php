<?php
  ob_start();

  include('indexSuccessHeader.xml.php');
  include('indexSuccessBody.xml.php');

  $result = ob_get_contents();
  ob_end_clean();
  echo Qubit::tidyXml($result);
?>
