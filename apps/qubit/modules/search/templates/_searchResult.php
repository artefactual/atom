<?php use_helper('Text') ?>

<div class="result">

  <?php if (isset($doc['digitalObject']['thumbnailPath'])): ?>
    <?php echo link_to(
      image_tag(
        $doc['digitalObject']['thumbnailPath'],
        array('alt' => 'image-thumb', 'width' => '150', 'height' => '150')),
      array('module' => 'informationobject', 'slug' => $doc['slug']),
      array('title' => get_search_i18n($doc, 'title'))) ?>
  <?php endif; ?>

  <h3><?php echo link_to(get_search_i18n($doc, 'title'), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></h3>

  <ul class="breadcrumb">
    <?php foreach($doc['ancestors'] as $id): ?>
      <?php if ($id == QubitInformationObject::ROOT_ID) continue ?>
      <li><?php echo link_to($pager->ancestors[$id]['title'], array('module' => 'informationobject', 'slug' => $pager->ancestors[$id]['slug']), array('title' => $pager->ancestors[$id]['title'])) ?></li>
    <?php endforeach; ?>
  </ul>

  <?php if (null !== $scopeAndContent = get_search_i18n($doc, 'scopeAndContent')): ?>
    <p><?php echo truncate_text($scopeAndContent, 250) ?></p>
  <?php endif; ?>

  <p>
    <?php if (isset($doc['dates'])): ?>
      <?php echo Qubit::renderDateStartEnd(null, $doc['dates'][0]['startDate'], $doc['dates'][0]['endDate']) ?>
    <?php endif; ?>
    <?php if (isset($doc['creators'][$sf_user->getCulture()])): ?>
      <?php echo __('by %1%', array('%1%' => $doc['creators'][$sf_user->getCulture()]['i18n'][0]['authorizedFormOfName'])) ?>
    <?php endif; ?>
  </p>

</div>

<?php if (false): ?>
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
<?php endif; ?>
