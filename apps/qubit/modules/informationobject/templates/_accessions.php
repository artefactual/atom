<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('Accession number(s)')); ?>
  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
      <?php foreach ($accessions as $item) { ?>
        <li><?php echo link_to(render_title($item->object), [$item->object, 'module' => 'accession']); ?></li>
      <?php } ?>
    </ul>
  </div>
</div>
