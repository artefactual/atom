<div id="search-stats">
  <?php if (0 < $pager->getNbResults()): ?>
    <?php echo __('Showing results %1% to %2% of %3% (%4% seconds)', array(
      '%1%' => $pager->getFirstIndice(),
      '%2%' => $pager->getLastIndice(),
      '%3%' => $pager->getNbResults(),
      '%4%' => $timer->elapsed()
      )) ?>
  <?php else: ?>
    <?php echo __('No results') ?>
  <?php endif; ?>
  <?php if ('print' == $sf_request->getGetParameter('media') && $pager->haveToPaginate()): ?>
    <?php echo __('(Only showing first %1% results for performance reasons)',
      array('%1%' => $pager->getMaxPerPage())) ?>
  <?php endif; ?>
</div>

<div class="section">
  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getDocument(); ?>
    <div class="clearfix search-results <?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">

      <?php if ('true' == $doc->hasDigitalObject): ?>
        <?php if (isset($doc->mediaTypeId)): ?>
          <?php if (QubitTerm::AUDIO_ID == $doc->mediaTypeId): ?>
            <?php echo link_to(image_tag('play.png', array('alt' => $doc->title)), array('slug' => $doc->slug, 'module' => 'informationobject')) ?>
          <?php elseif (!empty($doc->thumbnailPath)): ?>
            <?php echo link_to(image_tag(public_path($doc->thumbnailPath), array('alt' => $doc->title)), array('slug' => $doc->slug, 'module' => 'informationobject')) ?>
          <?php endif; ?>
        <?php else: ?>
          <?php echo link_to(image_tag(QubitDigitalObject::getGenericRepresentation(null, QubitTerm::THUMBNAIL_ID)->getFullPath(), array('alt' => $doc->title)), array('slug' => $doc->slug, 'module' => 'informationobject')) ?>
        <?php endif; ?>
      <?php endif; ?>

      <h2><?php echo link_to(render_title($doc->title), array('slug' => $doc->slug, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $doc->publicationStatusId): ?> <span class="publicationStatus">draft</span><?php endif; ?></h2>

      <?php if ($doc->scopeAndContent): ?>
        <div class="field">
          <?php echo highlight_text(truncate_text($doc->scopeAndContent, 256), $sf_request->query) ?>
        </div>
      <?php endif; ?>

      <?php if ($doc->referenceCode): ?>
        <?php echo render_show(__('Reference code'), render_value($doc->referenceCode)); ?>
      <?php endif; ?>

      <?php $dates = unserialize($doc->dateSerialized) ?>
      <?php if (0 < count($dates)): ?>
        <div class="field">
          <h3><?php echo __('Date(s)') ?></h3>
          <div>
            <ul>
              <?php foreach ($dates as $item): ?>
                <li><?php printf('%s (%s)',
                  Qubit::renderDateStartEnd($item['date'], $item['start_date'], $item['end_date']),
                  QubitTerm::getById($item['type_id'])->__toString()) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>

      <?php $creators = unserialize($doc->creatorSerialized) ?>
      <?php if (0 < count($creators)): ?>
        <div class="field">
          <h3><?php echo __('Creator(s)') ?></h3>
          <div>
            <ul>
              <?php foreach ($creators as $item): ?>
                <li><?php echo link_to(render_title($item['name']), array('slug' => $item['slug'], 'module' => 'actor')) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($doc->levelOfDescription): ?>
        <?php echo render_show(__('Level of description'), render_value($doc->levelOfDescription)) ?>
      <?php endif; ?>

      <?php if (sfConfig::get('app_multi_repository') && null != $doc->repositoryId): ?>
        <?php echo render_show(__('Repository'), link_to(render_title($doc->repository), array('slug' => $doc->repositorySlug, 'module' => 'repository'))) ?>
      <?php endif; ?>

      <?php if ($doc->collectionRootSlug !== $doc->slug): ?>
        <?php echo render_show(__('Part of'), link_to(render_title($doc->partOf), array('slug' => $doc->collectionRootSlug, 'module' => 'informationobject'))) ?>
      <?php endif; ?>

    </div>
  <?php endforeach; ?>
</div>

<?php if ('print' != $sf_request->getGetParameter('media')): ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php endif; ?>
