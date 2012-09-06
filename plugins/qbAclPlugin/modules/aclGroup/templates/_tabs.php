<div class="section tabs" id="groupsAclTabs">

  <h2 class="element-invisible"><?php echo __('Groups ACL') ?></h2>

  <div class="content">
    <ul class="clearfix links">
    <?php foreach ($groupsMenu->getChildren() as $child): ?>
      <li<?php if (str_replace('%currentId%', $sf_request->id, $child->path) == $sf_context->getRouting()->getCurrentInternalUri()): ?> class="active"<?php endif; ?>><?php echo link_to($child->getLabel(array('cultureFallback' => true)), $child->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?></li>
    <?php endforeach; ?>
    </ul>
  </div>

</div>
