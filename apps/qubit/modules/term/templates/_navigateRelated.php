<ul class="nav nav-tabs">
  <li class="nav-item <?php if ('index' == $sf_context->getActionName()) { ?><?php echo 'active'; ?><?php } ?>">
    <?php if ($relatedIoCount || 'relatedAuthorities' == $sf_context->getActionName()) { ?>
      <?php echo link_to(__('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_informationobject'), '%2%' => $relatedIoCount]),
                   [$resource, 'module' => 'term', 'action' => 'index'], ['class' => 'nav-link']); ?>
    <?php } else { ?>
      <a class="nav-link" href="#"><?php echo __('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_informationobject'), '%2%' => $relatedIoCount]); ?></a>
    <?php } ?>
  </li>
  <li class="nav-item <?php if ('index' != $sf_context->getActionName()) { ?><?php echo 'active'; ?><?php } ?>">
    <?php if ($relatedActorCount) { ?>
      <?php echo link_to(__('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_actor'), '%2%' => $relatedActorCount]),
                   [$resource, 'module' => 'term', 'action' => 'relatedAuthorities'], ['class' => 'nav-link']); ?>
    <?php } else { ?>
      <a class="nav-link" href="#"><?php echo __('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_actor'), '%2%' => $relatedActorCount]); ?></a>
    <?php } ?>
  </li>
</ul>
