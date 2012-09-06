<?php if (QubitAcl::check($resource, 'update')): ?>
  <?php include_component('repository', 'uploadLimit', array('resource' => $resource)) ?>
<?php endif; ?>

<div class="section">

  <h3><?php echo sfConfig::get('app_ui_label_holdings') ?></h3>

  <div>

    <div class="search">
      <form action="<?php echo url_for(array($resource, 'module' => 'search')) ?>">
        <input type="text" name="query" value="<?php echo esc_entities($sf_request->query) ?>">
        <input type="submit" value="<?php echo __('Search') ?>" class="form-submit"/>
      </form>
    </div>

    <ul>
      <?php foreach ($holdings as $item): ?>
        <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $item->getPublicationStatus()->status ?></span><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>

    <?php echo get_partial('default/pager', array('pager' => $pager, 'small' => true)) ?>

  </div>

</div>
