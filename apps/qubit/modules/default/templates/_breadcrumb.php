<div id="breadcrumb">

  <h2 class="element-invisible"><?php echo __('You are here') ?></h2>

  <div class="content">
    <ol>
      <?php foreach ($objects as $object): ?>
        <?php if (isset($object->parent)): // FIXME Implement something like ->slice(1) or [1:] ?>
          <li><?php echo link_to(render_title($object), array($object, 'module' => 'informationobject')) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>

</div>
