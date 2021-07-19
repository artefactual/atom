<button
  class="btn atom-btn-white ms-auto clipboard"
  data-clipboard-slug="<?php echo $slug; ?>"
  data-clipboard-type="<?php echo $type; ?>"
  <?php if (!$showText) { ?>
    data-tooltip="<?php echo __('Add to clipboard'); ?>"
  <?php } ?>
  data-clipboard-add="<?php echo __('Add to clipboard'); ?>"
  data-clipboard-remove="<?php echo __('Remove from clipboard'); ?>">
  <i class="fas fa-lg fa-paperclip" aria-hidden="true"></i>
  <span class="<?php echo !$showText ? 'visually-hidden' : 'ms-1'; ?>">
    <?php echo __('Add to clipboard'); ?>
  </span>
</button>
