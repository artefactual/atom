<h1><?php echo __('Import completed') ?></h1>

<div class="note">
  <?php echo __('Elapsed time: %1% seconds.', array('%1%' => $timer->elapsed())) ?>
</div>

<?php if ($errors != null): ?>
  <?php if (!(count($errors) == 1 && $errors[0] == '..')): ?>
    <div class="messages error">
      <h3>Warnings were encountered:</h3>
      <?php foreach ($errors as $error): ?>
        <div>
          <?php echo $error ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<section class="actions">
  <ul>
    <?php if (isset($rootObject)): ?>
      <?php if (!($rootObject instanceof QubitTaxonomy)): ?>
        <li><?php echo link_to(__('View %1%', array('%1%' => sfConfig::get('app_ui_label_'.strtolower($objectType)))), array($rootObject, 'module' => strtolower($objectType)), array('class' => 'c-btn')) ?></li>
      <?php else: ?>
        <li><?php echo link_to(__('View %1%', array('%1%' => sfConfig::get('app_ui_label_'.strtolower($objectType)))), array($rootObject, 'module' => 'taxonomy'), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>
    <?php elseif (isset($sf_request->csvObjectType)): ?>
      <?php if ($sf_request->csvObjectType == 'informationObject'): ?>
        <li><?php echo link_to(__('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))), array('module' => 'informationobject', 'action' => 'browse'), array('class' => 'c-btn')) ?></li>
      <?php elseif ($sf_request->csvObjectType == 'authorityRecord'): ?>
        <li><?php echo link_to(__('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_actor'))), array('module' => 'actor', 'action' => 'browse'), array('class' => 'c-btn')) ?></li>
      <?php elseif ($sf_request->csvObjectType == 'accession'): ?>
        <li><?php echo link_to(__('Browse accessions'), array('module' => 'accession', 'action' => 'browse'), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>
    <?php endif; ?>
    <?php if (0 < count($errors)): ?>
      <li><?php echo link_to(__('Back'), array('module' => 'object', 'action' => 'importSelect'), array('class' => 'c-btn')) ?></li>
    <?php endif; ?>
  </ul>
</section>
