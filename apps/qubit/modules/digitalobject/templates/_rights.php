<section>

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('%1% (%2%) rights area', ['%1%' => sfConfig::get('app_ui_label_digitalobject'), '%2%' => $resource->usage]).'</h2>', [$resource, 'module' => 'digitalobject', 'action' => 'edit'], ['title' => __('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))])]); ?>

  <?php foreach ($resource->getRights() as $item) { ?>

    <?php echo get_partial('right/right', ['resource' => $item->object, 'object' => $item]); ?>

  <?php } ?>

</section>

<section>

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::REFERENCE_ID)) { ?>

    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('%1% (%2%) rights area', ['%1%' => sfConfig::get('app_ui_label_digitalobject'), '%2%' => $child->usage]).'</h2>', [$resource, 'module' => 'digitalobject', 'action' => 'edit'], ['title' => __('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))])]); ?>

    <?php foreach ($child->getRights() as $item) { ?>

      <?php echo get_partial('right/right', ['resource' => $item->object, 'object' => $resource]); ?>

    <?php } ?>

  <?php } ?>

</section>

<section>

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::THUMBNAIL_ID)) { ?>

    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('%1% (%2%) rights area', ['%1%' => sfConfig::get('app_ui_label_digitalobject'), '%2%' => $child->usage]).'</h2>', [$resource, 'module' => 'digitalobject', 'action' => 'edit'], ['title' => __('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))])]); ?>

    <?php foreach ($child->getRights() as $item) { ?>

      <?php echo get_partial('right/right', ['resource' => $item->object, 'object' => $resource]); ?>

    <?php } ?>

  <?php } ?>

</section>
