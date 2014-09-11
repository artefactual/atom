<?php use_helper('Text') ?>

<?php if (isset($doc['hasDigitalObject']) && true === $doc['hasDigitalObject']): ?>
  <article class="search-result has-preview">
<?php else: ?>
  <article class="search-result">
<?php endif; ?>

  <?php if (isset($doc['hasDigitalObject'])): ?>

    <div class="search-result-preview">

      <?php if (isset($doc['digitalObject']['thumbnailPath'])): ?>
        <a href="<?php echo url_for(array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>">
          <div class="preview-container">
            <?php echo image_tag($doc['digitalObject']['thumbnailPath']) ?>
          </div>
        </a>
      <?php endif; ?>
    </div>

  <?php endif; ?>

  <div class="search-result-description">

    <p class="title"><?php echo link_to(render_title(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture))), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></p>

    <ul class="result-details">

      <?php if ('1' == sfConfig::get('app_inherit_code_informationobject', 1)
        && isset($doc['referenceCode']) && !empty($doc['referenceCode'])) : ?>
          <li class="reference-code"><?php echo $doc['referenceCode'] ?></li>
      <?php elseif (isset($doc['identifier']) && !empty($doc['identifier'])) : ?>
          <li class="reference-code"><?php echo $doc['identifier'] ?></li>
      <?php endif; ?>

      <?php if (isset($doc['levelOfDescriptionId']) && !empty($doc['levelOfDescriptionId'])): ?>
        <li class="level-description"><?php echo QubitCache::getLabel($doc['levelOfDescriptionId'], 'QubitTerm') ?></li>
      <?php endif; ?>

      <?php if (isset($doc['dates'])): ?>
        <?php foreach ($doc['dates'] as $date): ?>
          <?php if (isset($date['startDateString'])
            || isset($date['endDateString'])
            || null != get_search_i18n($date, 'date', array('culture' => $culture))): ?>

            <li class="dates"><?php echo Qubit::renderDateStartEnd(get_search_i18n($date, 'date', array('culture' => $culture)),
              isset($date['startDateString']) ? $date['startDateString'] : null,
              isset($date['endDateString']) ? $date['endDateString'] : null) ?></li>

            <?php break; ?>

          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (isset($doc['publicationStatusId']) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $doc['publicationStatusId']): ?>
        <li class="publication-status"><?php echo QubitCache::getLabel($doc['publicationStatusId'], 'QubitTerm') ?></li>
      <?php endif; ?>

    </ul>

    <?php if (null !== $scopeAndContent = get_search_i18n($doc, 'scopeAndContent', array('culture' => $culture))): ?>
      <p><?php echo truncate_text($scopeAndContent, 250) ?></p>
    <?php endif; ?>

    <?php if (null !== $creationDetails = get_search_creation_details($doc, $culture)): ?>
      <p class="creation-details"><?php echo $creationDetails ?></p>
    <?php endif; ?>

  </div>

</article>
