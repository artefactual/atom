<th class="sortable" width="<?php echo $size ?>">

  <?php

    // Set a default if it has been defined
    if (empty($sf_request->sort) && !empty($default))
    {
      $sf_request->sort = $name.ucfirst($default);
    }

    $up   = "${name}Up";
    $down = "${name}Down";
    $next = $sf_request->sort !== $up ? $up : $down;

  ?>

  <?php echo link_to($label,
    array('sort' => $next) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
    array('title' => __('Sort'), 'class' => 'sortable')) ?>

  <?php if ($up === $sf_request->sort): ?>
    <?php echo image_tag('up.gif', array('alt' => __('Sort ascending'))) ?>
  <?php elseif ($down === $sf_request->sort): ?>
    <?php echo image_tag('down.gif', array('alt' => __('Sort descending'))) ?>
  <?php endif; ?>

</th>
