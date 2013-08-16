<ul class="nav nav-pills">
  <?php foreach ($userAclMenu->getChildren() as $item): ?>
    <li<?php if (str_replace('%currentSlug%', $sf_request->getAttribute('sf_route')->resource->slug, $item->path) == $sf_context->getRouting()->getCurrentInternalUri()): ?> class="active"<?php endif; ?>><?php echo link_to($item->getLabel(array('cultureFallback' => true)), $item->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?></li>
  <?php endforeach; ?>
</ul>
