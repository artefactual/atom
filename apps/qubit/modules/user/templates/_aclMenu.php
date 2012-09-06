<div class="section tabs" id="userAclMenu">

  <h2 class="element-invisible"><?php echo __('User ACL menu') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <?php foreach ($userAclMenu->getChildren() as $item): ?>
        <li<?php if (str_replace('%currentSlug%', $sf_request->getAttribute('sf_route')->resource->slug, $item->path) == $sf_context->getRouting()->getCurrentInternalUri()): ?> class="active"<?php endif; ?>><?php echo link_to($item->getLabel(array('cultureFallback' => true)), $item->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

</div>
