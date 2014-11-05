<section id="action-icons">
  <ul>

    <li>
      <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'reports')) ?>">
        <i class="icon-print"></i>
        <?php echo __('Reports') ?>
      </a>
    </li>

    <?php if ($sf_user->isAdministrator()): ?>
      <li class="separator"><h4><?php echo __('Import') ?></h4></li>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'xml')) ?>">
          <i class="icon-download-alt"></i>
          <?php echo __('XML') ?>
        </a>
      </li>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'csv')) ?>">
          <i class="icon-download-alt"></i>
          <?php echo __('CSV') ?>
        </a>
      </li>
    <?php endif; ?>

    <li class="separator"><h4><?php echo __('Export') ?></h4></li>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfDcPlugin')): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfDcPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('Dublin Core 1.1 XML') ?>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEadPlugin')): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfEadPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('EAD 2002 XML') ?>
        </a>
      </li>
    <?php endif; ?>

    <?php if ('sfModsPlugin' == $sf_context->getModuleName() && $sf_context->getConfiguration()->isPluginEnabled('sfModsPlugin')): ?>
      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfModsPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('MODS 3.3 XML') ?>
        </a>
      </li>
    <?php endif; ?>

    <?php if ($resource->parentId == QubitInformationObject::ROOT_ID): ?>
      <li class="separator"><h4><?php echo __('Finding aid') ?></h4></li>
      <?php $path = arGenerateFindingAid::getFindingAidPath($resource->id) ?>

      <?php if ($sf_user->isAuthenticated()): ?>
        <li>
          <a href="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'findingAid')) ?>">
            <i class="icon-cogs"></i>
            <?php echo __('Generate') ?>
          </a>
        </li>
      <?php endif; ?>

      <?php $faStatus = arGenerateFindingAid::getStatus($resource->id) ?>
      <?php $faStatusString = arGenerateFindingAid::getStatusString($resource->id) ?>

      <!-- Ensure file is actually there -->
      <?php if ($faStatus === QubitTerm::JOB_STATUS_COMPLETED_ID && !file_exists($path)): ?>
        <?php $faStatus = QubitTerm::JOB_STATUS_ERROR_ID ?>
        <?php $faStatusString = 'File missing' ?>
      <?php endif; ?>

      <?php if ($faStatus === QubitTerm::JOB_STATUS_COMPLETED_ID): ?>
        <li>
          <a href="<?php echo public_path($path) ?>">

            <i class="icon-upload-alt"></i>
            <?php echo __('Download'); ?>
          </a>
        </li>
      <?php else: ?>
        <li>
          <a>
            <i class="icon-info-sign"></i>
            <?php echo __('Status: ') . ucfirst($faStatusString); ?>
          </a>
        </li>
      <?php endif; ?>
    <?php endif; ?>

  </ul>
</section>
