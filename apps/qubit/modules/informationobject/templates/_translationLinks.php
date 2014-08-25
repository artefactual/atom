<?php if (count($translations) > 0): ?>
  <div class="translation-links">
    <?php echo __('Other languages available:') ?>
    <ul>
      <?php foreach ($translations as $culture => $title): ?>
        <li>
          <?php echo '['.$culture.']' ?>
          <?php echo link_to($title, array($resource, 'module' => 'informationobject', 'sf_culture' => $culture)) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
