<div class="btn-group translation-links">
  <button class="btn dropdown-toggle" data-toggle="dropdown">
    <?php echo __('Other languages available') ?>
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu">
    <?php foreach ($translations as $culture => $title): ?>
      <li>
        <?php echo link_to('['.$culture.'] - '.$title, array($resource, 'module' => 'informationobject', 'sf_culture' => $culture)) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
