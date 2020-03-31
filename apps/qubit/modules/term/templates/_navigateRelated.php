<ul class="nav nav-tabs">
  <li class="nav-item <?php if ($sf_context->getActionName() == 'index'): ?><?php echo "active" ?><?php endif; ?>">
    <?php if ($relatedIoCount || $sf_context->getActionName() == "relatedAuthorities"): ?>
      <?php echo link_to(__('Related Descriptions') . sprintf(' (%d)', $relatedIoCount),
                   array($resource, 'module' => 'term', 'action' => 'index'), array('class' => 'nav-link')) ?>
    <?php else: ?>
      <a class="nav-link" href="#"><?php echo __('Related Descriptions') . sprintf(' (%d)', $relatedIoCount) ?></a>
    <?php endif; ?>
  </li>
  <li class="nav-item <?php if ($sf_context->getActionName() != 'index'): ?><?php echo "active" ?><?php endif; ?>">
    <?php if ($relatedActorCount): ?>
      <?php echo link_to(__('Related Authorities') . sprintf(' (%d)', $relatedActorCount),
                   array($resource, 'module' => 'term', 'action' => 'relatedAuthorities'), array('class' => 'nav-link')) ?>
    <?php else: ?>
      <a class="nav-link" href="#"><?php echo __('Related Authorities') . sprintf(' (%d)', $relatedActorCount) ?></a>
    <?php endif; ?>
  </li>
</ul>
