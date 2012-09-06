<div>

  <div style="float: right;">

    <?php echo get_component('digitalobject', 'show', array(
      'iconOnly' => true,
      'link' => public_path($representation->getFullPath()),
      'resource' => $representation,
      'usageType' => QubitTerm::THUMBNAIL_ID)) ?>

  </div>

  <div>

    <?php echo render_show(__('Filename'), $representation->name) ?>

    <?php echo render_show(__('Filesize'), hr_filesize($representation->byteSize)) ?>

    <?php echo link_to(__('Delete'), array($representation, 'module' => 'digitalobject', 'action' => 'delete'), array('class' => 'delete')) ?>

  </div>

</div>
