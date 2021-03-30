<button class="<?php echo $class; ?>"
  data-clipboard-slug="<?php echo $slug; ?>"
  data-clipboard-type="<?php echo $type; ?>"
  <?php echo $tooltip ? 'data-toggle="tooltip"' : ''; ?>
  data-title="<?php echo $title; ?>"
  data-alt-title="<?php echo $altTitle; ?>">
  <?php echo $title; ?>
</button>
