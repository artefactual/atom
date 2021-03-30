<?php if ('QubitInformationObject' === $className) { ?>

  <?php foreach ($resource->getRights() as $right) { ?>

    <?php echo get_partial('right/right',
      [
          'resource' => $right->object,
          'inherit' => $item != $resource ? $item : null,
          'relatedObject' => $resource, ]); ?>

  <?php } ?>

<?php } elseif ('QubitAccession' === $className) { ?>

  <?php foreach ($ancestor->getRights() as $item) { ?>

    <?php echo get_partial('right/right',
      [
          'resource' => $item->object,
          'inherit' => 0 == count($resource->getRights()) ? $resource : null,
          'relatedObject' => $resource, ]); ?>

  <?php } ?>

<?php } ?>
