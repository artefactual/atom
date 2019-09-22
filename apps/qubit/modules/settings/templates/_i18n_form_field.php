<?php if (!empty($label)): ?>
  <?php echo $form->$name
    ->renderLabel($label) ?>
<?php endif; ?>

<?php if (strlen($error = $form->$name->renderError())): ?>
  <?php echo $error ?>
<?php endif; ?>

<?php if ($sourceCultureHelper = $settings[$name]->getSourceCultureHelper($sf_context->user->getCulture())): ?>
  <div class="default-translation"><?php echo $sourceCultureHelper ?></div>
<?php endif; ?>

<?php echo $form->$name->render() ?>
