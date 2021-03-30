<?php foreach ($filterTags as $name => $options) { ?>
  <?php echo get_component('search', 'filterTag', ['name' => $name, 'options' => $options]); ?>
<?php } ?>
