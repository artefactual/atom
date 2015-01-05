<?php if ($className === 'QubitInformationObject'): ?>

  <?php foreach ($resource->getRights() as $right): ?>

    <?php echo get_partial('right/right',
      array(
        'resource' => $right->object,
        'inherit' => $item != $resource ? $item : null,
        'informationObject' => $resource)) ?>

  <?php endforeach; ?>

<?php elseif ($className === 'QubitAccession'): ?>

  <?php foreach ($ancestor->getRights() as $item): ?>

    <?php echo get_partial('right/right',
      array(
        'resource' => $item->object,
        'inherit' => 0 == count($resource->getRights()) ? $resource : null)) ?>

  <?php endforeach; ?>

<?php endif; ?>
