<?php ob_start() ?>

<?php include('indexSuccessHeader.xml.php') ?>
<?php include('indexSuccessBody.xml.php') ?>

<?php $result = ob_get_contents() ?>
<?php ob_end_clean() ?>
<?php echo tidy_xml($result) ?>
