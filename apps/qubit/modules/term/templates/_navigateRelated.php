<nav>
  <ul class="nav nav-pills mb-3 d-flex gap-2">
    <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
    <?php if ('index' == $sf_context->getActionName()) { ?>
      <?php $options['class'] .= ' active'; ?>
      <?php $options['aria-current'] = 'page'; ?>
    <?php } ?>
    <li class="nav-item">
      <?php if ($relatedIoCount || 'relatedAuthorities' == $sf_context->getActionName()) { ?>
        <?php echo link_to(
            __('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_informationobject'), '%2%' => $relatedIoCount]),
            [$resource, 'module' => 'term', 'action' => 'index'],
            $options
        ); ?>
      <?php } else { ?>
        <a class="<?php echo $options['class']; ?>" href="#"><?php echo __('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_informationobject'), '%2%' => $relatedIoCount]); ?></a>
      <?php } ?>
    </li>
    <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
    <?php if ('index' != $sf_context->getActionName()) { ?>
      <?php $options['class'] .= ' active'; ?>
      <?php $options['aria-current'] = 'page'; ?>
    <?php } ?>
    <li class="nav-item">
      <?php if ($relatedActorCount) { ?>
        <?php echo link_to(
            __('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_actor'), '%2%' => $relatedActorCount]),
            [$resource, 'module' => 'term', 'action' => 'relatedAuthorities'],
            $options
        ); ?>
      <?php } else { ?>
        <a class="<?php echo $options['class']; ?>" href="#"><?php echo __('Related %1% (%2%)', ['%1%' => sfConfig::get('app_ui_label_actor'), '%2%' => $relatedActorCount]); ?></a>
      <?php } ?>
    </li>
  </ul>
</nav>
