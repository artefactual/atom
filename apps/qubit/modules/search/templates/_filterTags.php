<?php foreach ($filterTags as $name => $options): ?>
  <?php echo get_component('search', 'filterTag', array('name' => $name, 'options' => $options)) ?>
<?php endforeach; ?>
