<?php slot('google_analytics'); ?>
  ga('set', 'dimension<?php echo $dimensionIndex; ?>', '<?php echo $repository->getAuthorizedFormOfName(['sourceCulture' => true]); ?>');
<?php end_slot(); ?>
