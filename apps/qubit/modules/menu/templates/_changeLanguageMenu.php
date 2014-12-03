<div id="language-menu" data-toggle="tooltip" data-title="<?php echo __('Language') ?>">

  <a class="top-item" data-toggle="dropdown" data-target="#"><?php echo __('Language') ?></a>

  <div class="top-dropdown-container">

    <div class="top-dropdown-arrow">
      <div class="arrow"></div>
    </div>

    <div class="top-dropdown-header">
      <?php echo __('Language') ?>
    </div>

    <div class="top-dropdown-body">
      <ul>
        <?php foreach (sfConfig::getAll() as $name => $value): ?>
          <?php if ('app_i18n_languages' == substr($name, 0, 18)): ?>
            <li<?php if ($sf_user->getCulture() == $value): ?> class="active"<?php endif; ?>><?php echo link_to(format_language($value, $value), array('sf_culture' => $value) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="top-dropdown-bottom"></div>

  </div>

</div>
