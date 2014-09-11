<div class="btn-group translation-links">
  <button class="btn dropdown-toggle" data-toggle="dropdown">
    <?php echo __('Other languages available') ?>
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu">
    <?php foreach ($translations as $code => $value): ?>
      <li>
        <?php echo link_to($value['language'].' &raquo; '.$value['name'], array($resource, 'module' => $module, 'sf_culture' => $code)) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
