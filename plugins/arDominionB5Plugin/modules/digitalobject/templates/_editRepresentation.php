<div class="row">
  <div class="col-md-6">
    <?php if (isset($representation->name)) { ?>
      <div class="mb-3">
        <h3 class="fs-6 mb-2">
          <?php echo __('Filename'); ?>
        </h3>
        <span class="text-muted">
          <?php echo render_value_inline($representation->name); ?>
        </span>
      </div>
    <?php } ?>

    <?php if (isset($representation->byteSize)) { ?>
      <div class="mb-3">
        <h3 class="fs-6 mb-2">
          <?php echo __('Filesize'); ?>
        </h3>
        <span class="text-muted">
          <?php echo hr_filesize($representation->byteSize); ?>
        </span>
      </div>
    <?php } ?>

    <?php if (QubitTerm::CHAPTERS_ID == $representation->usageId) { ?>
      <a
        href="<?php echo $representation->getFullPath(); ?>"
        class="btn atom-btn-white me-2">
        <i class="fas fa-fw fa-eye me-1" aria-hidden="true"></i>
        <?php echo __('View file'); ?>
      </a>
    <?php } ?>
    <a
      href="<?php echo url_for([
          $representation,
          'module' => 'digitalobject',
          'action' => 'delete',
      ]); ?>"
      class="btn atom-btn-white">
      <i class="fas fa-fw fa-times me-1" aria-hidden="true"></i>
      <?php echo __('Delete'); ?>
    </a>
  </div>
  <?php if (QubitTerm::CHAPTERS_ID != $representation->usageId) { ?>
    <div class="col-md-6 mt-3 mt-md-0">
      <?php echo get_component('digitalobject', 'show', [
          'iconOnly' => true,
          'link' => public_path($representation->getFullPath()),
          'resource' => $representation,
          'usageType' => QubitTerm::THUMBNAIL_ID,
          'editForm' => true,
      ]); ?>
    </div>
  <?php } ?>
</div>
