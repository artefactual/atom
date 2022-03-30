<li class="nav-item dropdown d-flex flex-column">
  <a
    class="nav-link dropdown-toggle d-flex align-items-center p-0"
    href="#"
    id="language-menu"
    role="button"
    data-bs-toggle="dropdown"
    aria-expanded="false">
    <i
      class="fas fa-2x fa-fw fa-globe-europe px-0 px-lg-2 py-2"
      data-bs-toggle="tooltip"
      data-bs-placement="bottom"
      data-bs-custom-class="d-none d-lg-block"
      title="<?php echo __('Language'); ?>"
      aria-hidden="true">
    </i>
    <span class="d-lg-none mx-1" aria-hidden="true">
      <?php echo __('Language'); ?>
    </span>
    <span class="visually-hidden">
      <?php echo __('Language'); ?>
    </span>  
  </a>
  <ul class="dropdown-menu dropdown-menu-end mb-2" aria-labelledby="language-menu">
    <li>
      <h6 class="dropdown-header">
        <?php echo __('Language'); ?>
      </h6>
    </li>
    <?php foreach ($langCodes as $value) { ?>
      <li>
        <?php echo link_to(
            ucfirst(format_language($value, $value)),
            ['sf_culture' => $value]
                + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
            ['class' => 'dropdown-item']
        ); ?>
      </li>
    <?php } ?>
  </ul>
</li>
