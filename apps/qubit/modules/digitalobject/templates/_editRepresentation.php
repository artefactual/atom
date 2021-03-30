<div>

  <div class="digital-object-preview">

    <?php if (QubitTerm::CHAPTERS_ID == $representation->usageId) { ?>
      <a href="<?php echo $representation->getFullPath(); ?>"><?php echo __('View file'); ?></a>

    <?php } else { ?>
      <?php echo get_component('digitalobject', 'show', [
          'iconOnly' => true,
          'link' => public_path($representation->getFullPath()),
          'resource' => $representation,
          'usageType' => QubitTerm::THUMBNAIL_ID, ]); ?>

    <?php } ?>
  </div>

  <div>

    <?php echo render_show(__('Filename'), render_value($representation->name)); ?>

    <?php echo render_show(__('Filesize'), hr_filesize($representation->byteSize)); ?>

    <?php echo link_to(__('Delete'), [$representation, 'module' => 'digitalobject', 'action' => 'delete'], ['class' => 'delete']); ?>

  </div>

</div>
