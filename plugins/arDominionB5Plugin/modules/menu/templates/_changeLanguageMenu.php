<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="language-menu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <?php echo __('Language'); ?>
  </a>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="language-menu">
    <li>
      <h5 class="dropdown-item-text">
        <?php echo __('Language'); ?>
      </h5>
    </li>
    <li><hr class="dropdown-divider"></li>
    <?php foreach ($langCodes as $value) { ?>
      <li>
        <?php echo link_to(
            ucfirst(format_language($value, $value)),
            ['sf_culture' => $value] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
            ['class' => 'dropdown-item']
        ); ?>
      </li>
    <?php } ?>
  </ul>
</li>
