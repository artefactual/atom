<section>

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object (%1%) rights area', array('%1%' => $resource->usage)).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

  <?php foreach ($resource->getRights() as $item): ?>

    <?php echo get_partial('right/right', array('resource' => $item->object, 'object' => $item)) ?>

  <?php endforeach; ?>

</section>

<section>

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::REFERENCE_ID)): ?>

    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object (%1%) rights area', array('%1%' => $child->usage)).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

    <?php foreach ($child->getRights() as $item): ?>

      <?php echo get_partial('right/right', array('resource' => $item->object, 'object' => $resource)) ?>

    <?php endforeach; ?>

  <?php endif; ?>

</section>

<section>

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::THUMBNAIL_ID)): ?>

    <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object (%1%) rights area', array('%1%' => $child->usage)).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

    <?php foreach ($child->getRights() as $item): ?>

      <?php echo get_partial('right/right', array('resource' => $item->object, 'object' => $resource)) ?>

    <?php endforeach; ?>

  <?php endif; ?>

</section>
