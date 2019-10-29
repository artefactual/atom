<?php use_helper('Text') ?>

<?php $doc = $hit->getData() ?>
<?php if (!empty($doc['hasDigitalObject'])): ?>
  <article class="search-result has-preview">
<?php else: ?>
  <article class="search-result">
<?php endif; ?>

  <?php if (!empty($doc['hasDigitalObject'])): ?>
    <div class="search-result-preview">
      <a href="<?php echo url_for(array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>">
        <div class="preview-container">
          <?php if (isset($doc['digitalObject']['thumbnailPath']) &&
                    QubitAcl::check(QubitInformationObject::getById($hit->getId()), 'readThumbnail') &&
                    QubitGrantedRight::checkPremis($hit->getId(), 'readThumb')): ?>
            <?php echo image_tag($doc['digitalObject']['thumbnailPath'],
              array('alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture))), 100))) ?>
          <?php else: ?>
            <?php echo image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($doc['digitalObject']['mediaTypeId']),
              array('alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture))), 100))) ?>
          <?php endif; ?>
        </div>
      </a>
    </div>
  <?php endif; ?>

  <div class="search-result-description">

    <p class="title"><?php echo link_to(render_title(get_search_i18n($doc, 'title', array('allowEmpty' => false, 'culture' => $culture))), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></p>

    <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => false)) ?>

    <ul class="result-details">

      <?php if ('1' == sfConfig::get('app_inherit_code_informationobject', 1)
        && isset($doc['referenceCode']) && !empty($doc['referenceCode'])) : ?>
          <li class="reference-code"><?php echo $doc['referenceCode'] ?></li>
      <?php elseif (isset($doc['identifier']) && !empty($doc['identifier'])) : ?>
          <li class="reference-code"><?php echo $doc['identifier'] ?></li>
      <?php endif; ?>

      <?php if (isset($doc['levelOfDescriptionId']) && !empty($doc['levelOfDescriptionId'])): ?>
        <li class="level-description"><?php echo render_value_inline(QubitCache::getLabel($doc['levelOfDescriptionId'], 'QubitTerm')) ?></li>
      <?php endif; ?>

      <?php if (isset($doc['dates'])): ?>
        <?php $date = render_search_result_date($doc['dates']) ?>
        <?php if (!empty($date)): ?>
          <li class="dates"><?php echo render_value_inline($date) ?></li>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (isset($doc['publicationStatusId']) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $doc['publicationStatusId']): ?>
        <li class="publication-status"><?php echo render_value_inline(QubitCache::getLabel($doc['publicationStatusId'], 'QubitTerm')) ?></li>
      <?php endif; ?>
      <?php if (isset($doc['partOf'])): ?>
        <p><?php echo __('Part of '), link_to(render_title(get_search_i18n($doc['partOf'], 'title',
                 array('allowEmpty' => false, 'culture' => $culture, 'cultureFallback' => true))),
                 array('slug' => $doc['partOf']['slug'], 'module' => 'informationobject')) ?></p>
      <?php endif; ?>
    </ul>

    <?php if (null !== $scopeAndContent = get_search_i18n($doc, 'scopeAndContent', array('culture' => $culture))): ?>
      <div class="scope-and-content"><?php echo render_value($scopeAndContent) ?></div>
    <?php endif; ?>

    <?php if (isset($doc['creators']) && null !== $creationDetails = get_search_creation_details($doc, $culture)): ?>
      <p class="creation-details"><?php echo render_value_inline($creationDetails) ?></p>
    <?php endif; ?>

  </div>

</article>
