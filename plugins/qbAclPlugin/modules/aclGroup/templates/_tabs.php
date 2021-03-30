<ul class="nav nav-pills">

  <?php foreach ($groupsMenu->getChildren() as $child) { ?>
    <li<?php if (str_replace('%currentId%', $sf_request->id, $child->path) == $sf_context->getRouting()->getCurrentInternalUri()) { ?> class="active"<?php } ?>><?php echo link_to($child->getLabel(['cultureFallback' => true]), $child->getPath(['getUrl' => true, 'resolveAlias' => true])); ?></li>
  <?php } ?>

</ul>
