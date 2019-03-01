<?php foreach ($filterTags as $param => $config): ?>
  <?php echo get_component('search', 'filterTag', array('model' => $config['model'],'object' => $config['object'], 'label' => $config['label'], 'param' => $param)) ?>
<?php endforeach; ?>
