<?php decorate_with('layout_1col') ?>

<?php slot('content') ?>

  <h2>SERVER</h2>
  <pre><?php echo $server ?></pre>

  <h2>PARAMETERS</h2>
  <pre><?php echo $params ?></pre>

  <h2>CONTENT</h2>
  <pre><?php echo $content ?></pre>

<?php end_slot() ?>
