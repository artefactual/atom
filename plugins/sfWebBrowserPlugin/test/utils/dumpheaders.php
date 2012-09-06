<pre>
<?php
ob_start("ob_gzhandler");

print_r($_SERVER);

print_r($_POST);

$putdata = urldecode(file_get_contents("php://input","r"));
print_r($putdata);

print_r($_FILES);
print_r(apache_request_headers());
