<div id="language-menu" data-toggle="tooltip" data-title="<?php echo __('Language') ?>">

  <button class="top-item" data-toggle="dropdown" data-target="#" aria-expanded="false"><?php echo __('Language') ?></button>

  <div class="top-dropdown-container">

    <div class="top-dropdown-arrow">
      <div class="arrow"></div>
    </div>

    <div class="top-dropdown-header">
      <h2><?php echo __('Language') ?></h2>
    </div>

    <div class="top-dropdown-body">
      <ul>
        <?php foreach ($langCodes as $value): ?>
          <li<?php if ($sf_user->getCulture() == $value): ?> class="active"<?php endif; ?>>
            <?php echo link_to(format_language($value, $value), array('sf_culture' => $value) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="top-dropdown-bottom"></div>

  </div>

</div>
