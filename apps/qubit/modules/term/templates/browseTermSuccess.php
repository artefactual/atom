<?php use_helper('Text') ?>

<h1><?php echo render_title($resource->taxonomy) ?> - <?php echo render_title($resource) ?></h1>

<div class="section">
  <?php foreach ($informationObjects as $item): ?>
    <div class="clearfix <?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
    
      <?php if (isset($item->digitalObjects[0]) && null !== $item->digitalObjects[0]->thumbnail): ?>
        <?php echo link_to(image_tag(public_path($item->digitalObjects[0]->thumbnail->getFullPath()), array('alt' => render_title($item))), array($item, 'module' => 'informationobject')) ?>
      <?php endif; ?>

      <h2><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $item->getPublicationStatus()->status ?></span><?php endif; ?></h2>

      <div>
        <?php echo truncate_text($item->scopeAndContent, 250) ?>
      </div>

      <?php $isad = new sfIsadPlugin($item); echo render_show(__('Reference code'), render_value($isad->referenceCode)) ?>

      <div class="field">
        <h3><?php echo __('Date(s)') ?></h3>
        <div>
          <ul>
            <?php foreach ($item->getDates() as $date): ?>
              <li>

                <?php echo Qubit::renderDateStartEnd($date->getDate(array('cultureFallback' => true)), $date->startDate, $date->endDate) ?> (<?php echo $date->getType(array('cultureFallback' => true)) ?>)

                <?php if (isset($date->actor)): ?>
                  <?php echo link_to(render_title($date->actor), array($date->actor, 'module' => 'actor')) ?>
                <?php endif; ?>

              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <?php echo render_show(__('Level of description'), render_value($item->levelOfDescription)) ?>

      <?php if (sfConfig::get('app_multi_repository') && isset($item->repository)): ?>
        <?php echo render_show(__('Repository'), link_to(render_title($item->repository), array($item->repository, 'module' => 'repository'))) ?>
      <?php endif; ?>

      <?php if ($item->getCollectionRoot() !== $item): ?>
        <?php echo render_show(__('Part of'), link_to(render_title($item->getCollectionRoot()), array($item->getCollectionRoot(), 'module' => 'informationobject'))) ?>
      <?php endif; ?>

    </div>
  <?php endforeach; ?>
</div>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <li><?php echo link_to(__('Browse all %1%', array('%1%' => render_title($resource->taxonomy))), array('module' => 'taxonomy', 'action' => 'browse', 'id' => $resource->taxonomy->id)) // HACK Use id deliberately because "Subjects" and "Places" menus still use id ?></li>
    </ul>
  </div>

</div>
