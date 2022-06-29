<nav>
  <ul class="nav nav-pills mb-3 d-flex gap-2">
    <?php foreach ($userAclMenu->getChildren() as $item) { ?>
      <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
      <?php if (
          str_replace(
              '%currentSlug%',
              $sf_request->getAttribute('sf_route')->resource->slug,
              $item->path
          )
          == $sf_context->getRouting()->getCurrentInternalUri()
      ) { ?>
        <?php $options['class'] .= ' active'; ?>
        <?php $options['aria-current'] = 'page'; ?>
      <?php } ?>
      <li class="nav-item">
        <?php echo link_to(
            $item->getLabel(['cultureFallback' => true]),
            $item->getPath(['getUrl' => true, 'resolveAlias' => true]),
            $options
        ); ?>
      </li>
    <?php } ?>
  </ul>
</nav>
