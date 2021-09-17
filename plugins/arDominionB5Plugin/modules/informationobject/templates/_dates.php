<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('Date(s)')); ?>

    <div xmlns:dc="http://purl.org/dc/elements/1.1/" about="<?php echo url_for([$resource, 'module' => 'informationobject'], true); ?>" class="<?php echo render_b5_show_value_css_classes(); ?>">

    <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
      <?php foreach ($resource->getDates() as $item) { ?>
        <li>
          <div class="date">
            <span property="dc:date" start="<?php echo $item->startDate; ?>" end="<?php echo $item->endDate; ?>"><?php echo render_value_inline(Qubit::renderDateStartEnd($item->getDate(['cultureFallback' => true]), $item->startDate, $item->endDate)); ?></span>
            <?php if ('dc' !== sfConfig::get('app_default_template_informationobject')) { ?>
              <span class="date-type">(<?php echo render_value_inline($item->type->__toString()); ?>)</span>
            <?php } ?>
            <dl class="mb-0">
              <?php if (isset($item->actor) && null !== $item->type->getRole()) { ?>
                <dt class="fw-normal text-muted"><?php echo render_value_inline($item->type->getRole()); ?></dt>
                <dd class="mb-0"><?php echo render_title($item->actor); ?></dd>
              <?php } ?>
              <?php if (null !== $item->getPlace()) { ?>
                <dt class="fw-normal text-muted"><?php echo __('Place'); ?></dt>
                <dd class="mb-0"><?php echo render_value_inline($item->getPlace()); ?></dd>
              <?php } ?>
              <?php if (0 < strlen($item->description)) { ?>
                <dt class="fw-normal text-muted"><?php echo __('Note'); ?></dt>
                <dd class="mb-0"><?php echo render_value_inline($item->description); ?></dd>
              <?php } ?>
            </dl>

          </div>
        </li>
      <?php } ?>
    </ul>

  </div>
</div>
