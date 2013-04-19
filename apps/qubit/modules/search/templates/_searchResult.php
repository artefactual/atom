<?php use_helper('Text') ?>

<?php if (isset($doc['hasDigitalObject']) && true === $doc['hasDigitalObject']): ?>
  <article class="search-result has-preview">
<?php else: ?>
  <article class="search-result">
<?php endif; ?>

  <?php if (isset($doc['hasDigitalObject'])): ?>

    <div class="search-result-preview">

      <?php if (isset($doc['digitalObject']['thumbnailPath'])): ?>
        <?php echo link_to(
          image_tag($doc['digitalObject']['thumbnailPath']),
          array('module' => 'informationobject', 'slug' => $doc['slug']),
          array('title' => get_search_i18n($doc, 'title'))) ?>
      <?php endif; ?>

    </div>

  <?php endif; ?>

  <div class="search-result-description">

    <p class="title"><?php echo link_to(render_title(get_search_i18n($doc, 'title')), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></p>

    <ul class="result-details">

      <?php if (isset($doc['referenceCode'])): ?>
        <li class="reference-code"><?php echo $doc['referenceCode'] ?></li>
      <?php endif; ?>

      <?php if (isset($doc['levelOfDescriptionId'])): ?>
        <li class="level-description"><?php echo $pager->levelsOfDescription[$doc['levelOfDescriptionId']] ?></li>
      <?php endif; ?>

      <?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $doc['publicationStatusId']): ?>
        <li class="publication-status"><?php echo $doc['publicationStatusId'] ?></li>
      <?php endif; ?>

      <?php if (isset($doc['dates'])): ?>
        <li class="dates"><?php # echo Qubit::renderDateStartEnd(null, $doc['dates'][0]['startDate'], $doc['dates'][0]['endDate']) ?></li>
      <?php endif; ?>

    </ul>

    <?php if (null !== $scopeAndContent = get_search_i18n($doc, 'scopeAndContent')): ?>
      <p><?php echo truncate_text($scopeAndContent, 250) ?></p>
    <?php endif; ?>

    <!-- TODO: show repo if multirepo and fix breadcrumb style -->
    <?php if (false): ?>
    <ul class="breadcrumb">
      <?php foreach($doc['ancestors'] as $id): ?>
        <?php if ($id == QubitInformationObject::ROOT_ID) continue ?>
        <li><?php echo link_to($pager->ancestors[$id]['title'], array('module' => 'informationobject', 'slug' => $pager->ancestors[$id]['slug']), array('title' => $pager->ancestors[$id]['title'])) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  </div>

</article>
