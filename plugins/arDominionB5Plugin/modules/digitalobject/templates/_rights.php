<?php
    // TODO: Move this to the controller when we only have B5 themes
    $headingsCondition = SecurityPrivileges::editCredentials($sf_user, 'informationObject');
    $headingsUrl = [$resource, 'module' => 'digitalobject', 'action' => 'edit'];
    $headingsTitle = __('Edit %1%', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]);
?>

<section>

  <?php echo render_b5_section_heading(
      __('%1% (%2%) rights area', ['%1%' => sfConfig::get('app_ui_label_digitalobject'), '%2%' => $resource->usage]),
      $headingsCondition,
      $headingsUrl,
      ['title' => $headingsTitle]
  ); ?>

  <?php foreach ($resource->getRights() as $item) { ?>

    <?php echo get_partial('right/right', ['resource' => $item->object, 'object' => $item]); ?>

  <?php } ?>

</section>

<section>

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::REFERENCE_ID)) { ?>

    <?php echo render_b5_section_heading(
        __('%1% (%2%) rights area', ['%1%' => sfConfig::get('app_ui_label_digitalobject'), '%2%' => $child->usage]),
        $headingsCondition,
        $headingsUrl,
        ['title' => $headingsTitle]
    ); ?>

    <?php foreach ($child->getRights() as $item) { ?>

      <?php echo get_partial('right/right', ['resource' => $item->object, 'object' => $resource]); ?>

    <?php } ?>

  <?php } ?>

</section>

<section>

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::THUMBNAIL_ID)) { ?>

    <?php echo render_b5_section_heading(
        __('%1% (%2%) rights area', ['%1%' => sfConfig::get('app_ui_label_digitalobject'), '%2%' => $child->usage]),
        $headingsCondition,
        $headingsUrl,
        ['title' => $headingsTitle]
    ); ?>

    <?php foreach ($child->getRights() as $item) { ?>

      <?php echo get_partial('right/right', ['resource' => $item->object, 'object' => $resource]); ?>

    <?php } ?>

  <?php } ?>

</section>
