<div class="section" id="languageMenu">

  <h2 class="element-invisible"><?php echo __('Language') ?></h2>

  <div class="content">
    <ul class="links">
      <?php foreach (sfConfig::getAll() as $name => $value): ?>
        <?php if ('app_i18n_languages' == substr($name, 0, 18)): ?>
          <li<?php if ($sf_user->getCulture() == $value): ?> class="active"<?php endif; ?>><?php echo link_to(format_language($value, $value), array('sf_culture' => $value) + $sf_request->getParameterHolder()->getAll()) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
