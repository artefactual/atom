<?php echo $form->$name
  ->renderLabel($label) ?>

<?php if ($sourceCultureHelper = $settings[$name]->getSourceCultureHelper($sf_context->user->getCulture())): ?>
  <div class="default-translation"><?php echo $sourceCultureHelper ?></div>
<?php endif; ?>

<?php echo $form->$name
  ->render() ?>
