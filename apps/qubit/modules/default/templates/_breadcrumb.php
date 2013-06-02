<section class="breadcrumb">

  <ul>
    <?php foreach ($objects as $object): ?>
      <?php if (isset($object->parent)): // FIXME Implement something like ->slice(1) or [1:] ?>
        <?php if (isset($resource) && $object == $resource): ?>
          <li class="active"><span><?php echo render_title($object) ?></span></li>
        <?php else: ?>
          <li><?php echo link_to(render_title($object), array($object, 'module' => 'informationobject')) ?></li>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>

</section>
