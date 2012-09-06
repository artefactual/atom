<h1><?php echo __('Repository harvest')?></h1>
<h3><?php echo __('Repository: %name%', array('%name%' => $repositoryName)) ?></h3>
<?php echo __('Repository harvested successfuly') ?>
<br/>
<?php if($noRecordsMatch == true):?>
  <?php echo __('No new records') ?>
<?php elseif(isset($recordCount)): ?>
<?php echo __('%count% records imported', array('%count%' => $recordCount)) ?>
<?php $index = 0 ?>
<?php foreach($errorsFound as $errorFound): ?>
  <?php echo __('Error found in record')?>
<?php endforeach ?>
<?php else :?>
  <?php echo __('An error occured during harvest') ?>
<?php endif ?>
<br>
<?php echo link_to(__('Return to harvester main page'),'oai/harvesterList')?>
