<section class="breadcrumb">

  <ul>
    <?php foreach ($objects as $object) { ?>
      <?php if (isset($object->parent)) { ?>
        <?php if (isset($resource) && $object == $resource) { ?>
          <li class="active"><span><?php echo render_title($object); ?></span></li>
        <?php } else { ?>
          <li><?php echo link_to(render_title($object), [$object, 'module' => 'informationobject']); ?></li>
        <?php } ?>
      <?php } ?>
    <?php } ?>
  </ul>

</section>
