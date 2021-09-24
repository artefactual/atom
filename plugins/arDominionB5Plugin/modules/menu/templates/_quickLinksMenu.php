<li class="nav-item dropdown d-flex flex-column">
  <a
    class="nav-link dropdown-toggle d-flex align-items-center p-0"
    href="#"
    id="quick-links-menu"
    role="button"
    data-bs-toggle="dropdown"
    aria-expanded="false">
    <i
      class="fas fa-2x fa-fw fa-info-circle px-0 px-lg-2 py-2"
      data-bs-toggle="tooltip"
      data-bs-placement="bottom"
      data-bs-custom-class="d-none d-lg-block"
      title="<?php echo __('Quick links'); ?>"
      aria-hidden="true">
    </i>
    <span class="d-lg-none mx-1" aria-hidden="true">
      <?php echo __('Quick links'); ?>
    </span> 
    <span class="visually-hidden">
      <?php echo __('Quick links'); ?>
    </span>
  </a>
  <ul class="dropdown-menu dropdown-menu-end mb-2" aria-labelledby="quick-links-menu">
    <li>
      <h6 class="dropdown-header">
        <?php echo __('Quick links'); ?>
      </h6>
    </li>
    <?php foreach ($quickLinks as $child) { ?>
      <?php if (
          'login' != $child->getName()
          && 'logout' != $child->getName()
          && 'myProfile' != $child->getName()
      ) { ?>
        <li <?php echo isset($child->name) ? 'id="node_'.$child->name.'"' : ''; ?>>
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
