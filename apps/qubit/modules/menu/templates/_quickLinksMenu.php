<div id="quick-links-menu" data-toggle="tooltip" data-title="<?php echo __('Quick links'); ?>">

  <button class="top-item" data-toggle="dropdown" data-target="#" aria-expanded="false"><?php echo __('Quick links'); ?></button>

  <div class="top-dropdown-container">

    <div class="top-dropdown-arrow">
      <div class="arrow"></div>
    </div>

    <div class="top-dropdown-header">
      <h2><?php echo __('Quick links'); ?></h2>
    </div>

    <div class="top-dropdown-body">
      <ul>
        <?php foreach ($quickLinks as $child) { ?>
          <?php if ('login' != $child->getName() && 'logout' != $child->getName() && 'myProfile' != $child->getName()) { ?>
            <li<?php if ($child->isSelected()) { ?> class="active"<?php } ?>><?php echo link_to($child->getLabel(['cultureFallback' => true]), $child->getPath(['getUrl' => true, 'resolveAlias' => true])); ?></li>
          <?php } ?>
        <?php } ?>
      </ul>
    </div>

    <div class="top-dropdown-bottom"></div>

  </div>

</div>
