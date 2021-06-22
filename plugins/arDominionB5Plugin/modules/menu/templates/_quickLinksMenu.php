<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="quick-links-menu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-2x fa-info-circle" aria-hidden="true"></i>
    <span class="sr-only"><?php echo __('Quick links'); ?></span>   
  </a>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quick-links-menu">
    <li>
      <h5 class="dropdown-item-text">
        <?php echo __('Quick links'); ?>
      </h5>
    </li>
    <li><hr class="dropdown-divider"></li>
    <?php foreach ($quickLinks as $child) { ?>
      <?php if ('login' != $child->getName() && 'logout' != $child->getName() && 'myProfile' != $child->getName()) { ?>
        <li>
          <?php echo link_to(
              $child->getLabel(['cultureFallback' => true]),
              $child->getPath(['getUrl' => true, 'resolveAlias' => true]),
              ['class' => 'dropdown-item']
          ); ?>
        </li>
      <?php } ?>
    <?php } ?>
  </ul>
</li>
