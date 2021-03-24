<div>

  <div class="digital-object-preview">

    <?php if ($representation->usageId == QubitTerm::CHAPTERS_ID): ?>
      <a href="<?php echo $representation->getFullPath() ?>"><?php echo __('View file') ?></a>

    <?php else: ?>
      <?php echo get_component('digitalobject', 'show', array(
        'iconOnly' => true,
        'link' => public_path($representation->getFullPath()),
        'resource' => $representation,
        'usageType' => QubitTerm::THUMBNAIL_ID)) ?>

    <?php endif; ?>
  </div>

  <div>

    <?php echo render_show(__('Filename'), render_value($representation->name)) ?>

    <?php echo render_show(__('Filesize'), hr_filesize($representation->byteSize)) ?>

    <?php echo link_to(__('Delete'), array($representation, 'module' => 'digitalobject', 'action' => 'delete'), array('class' => 'delete')) ?>

  </div>

</div>
