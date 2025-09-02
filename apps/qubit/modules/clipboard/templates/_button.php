<button
  class="btn atom-btn-white ms-auto active-primary clipboard"
  data-clipboard-slug="<?php echo $slug; ?>"
  data-clipboard-type="<?php echo $type; ?>"
  <?php echo $wide ? '' : 'data-tooltip="true"'; ?>
  data-title="<?php echo $wide ? __('Add') : __('Add to clipboard'); ?>"
  data-alt-title="<?php echo $wide ? __('Remove') : __('Remove from clipboard'); ?>">
  <i class="fas fa-lg fa-paperclip" aria-hidden="true"></i>
  <span class="<?php echo $wide ? 'ms-2' : 'visually-hidden'; ?>">
    <?php echo $wide ? __('Add') : __('Add to clipboard'); ?>
  </span>
</button>
