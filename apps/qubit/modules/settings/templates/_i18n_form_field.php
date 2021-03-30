<?php if (!empty($label)) { ?>
  <?php echo $form->{$name}
    ->renderLabel($label); ?>
<?php } ?>

<?php if (strlen($error = $form->{$name}->renderError())) { ?>
  <?php echo $error; ?>
<?php } ?>

<?php if ($sourceCultureHelper = $settings[$name]->getSourceCultureHelper($sf_context->user->getCulture())) { ?>
  <div class="default-translation"><?php echo $sourceCultureHelper; ?></div>
<?php } ?>

<?php echo $form->{$name}->render(); ?>
