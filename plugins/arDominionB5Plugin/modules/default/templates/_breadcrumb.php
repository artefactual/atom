<nav aria-label="breadcrumb" id="breadcrumb">
  <ol class="breadcrumb">
    <?php foreach ($objects as $object) { ?>
      <?php if (isset($object->parent)) { ?>
        <?php if (isset($resource) && $object == $resource) { ?>
          <li class="breadcrumb-item active" aria-current="page">
            <?php echo render_title($object); ?>
          </li>
        <?php } else { ?>
          <li class="breadcrumb-item">
            <?php echo link_to(render_title($object), [$object, 'module' => 'informationobject']); ?>
          </li>
        <?php } ?>
      <?php } ?>
    <?php } ?>
  </ol>
</nav>
