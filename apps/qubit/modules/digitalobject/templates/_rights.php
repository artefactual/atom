<div class="section" style="margin-left: 2em;">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object (%1%) rights area', array('%1%' => $resource->usage)).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

  <?php foreach ($resource->getRights() as $item): ?>

    <?php echo get_partial('right/right', array('resource' => $item->object)) ?>

  <?php endforeach; ?>

</div>

<div class="section" style="margin-left: 2em;">

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::REFERENCE_ID)): ?>

    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object (%1%) rights area', array('%1%' => $child->usage)).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

    <?php foreach ($child->getRights() as $item): ?>

      <?php echo get_partial('right/right', array('resource' => $item->object)) ?>

    <?php endforeach; ?>

  <?php endif; ?>

</div>

<div class="section" style="margin-left: 2em;">

  <?php if ($child = $resource->getChildByUsageId(QubitTerm::THUMBNAIL_ID)): ?>

    <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Digital object (%1%) rights area', array('%1%' => $child->usage)).'</h2>', array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('title' => __('Edit digital object'))) ?>

    <?php foreach ($child->getRights() as $item): ?>

      <?php echo get_partial('right/right', array('resource' => $item->object)) ?>

    <?php endforeach; ?>

  <?php endif; ?>

</div>
