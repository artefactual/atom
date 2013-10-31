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

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <?php if (isset($rootObject)): ?>
        <?php if (!($rootObject instanceof QubitTaxonomy)): ?>
          <?php echo link_to(__('View %1%', array('%1%' => sfConfig::get('app_ui_label_'.strtolower($objectType)))), array($rootObject, 'module' => strtolower($objectType))) ?>
        <?php else: ?>
          <?php echo link_to(__('View %1%', array('%1%' => sfConfig::get('app_ui_label_'.strtolower($objectType)))), array($rootObject, 'module' => 'taxonomy')) ?>
        <?php endif; ?>
      <?php elseif (isset($sf_request->csvObjectType)): ?>
        <?php if ($sf_request->csvObjectType == 'informationObject'): ?>
          <?php echo link_to(__('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))), array('module' => 'informationobject', 'action' => 'browse')) ?>
        <?php elseif ($sf_request->csvObjectType == 'authorityRecord'): ?>
          <?php echo link_to(__('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_actor'))), array('module' => 'actor', 'action' => 'browse')) ?>
        <?php elseif ($sf_request->csvObjectType == 'accession'): ?>
          <?php echo link_to(__('Browse accessions'), array('module' => 'accession', 'action' => 'browse')) ?>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (0 < count($errors)): ?>
        <?php echo link_to(__('Back'), array('module' => 'object', 'action' => 'importSelect')) ?>
      <?php endif; ?>
    </ul>
  </div>

</div>
