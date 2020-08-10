<?php use_helper('Text') ?>

<?php if (!empty($doc['hasDigitalObject'])): ?>
  <article class="search-result has-preview">
<?php else: ?>
  <article class="search-result">
<?php endif; ?>

  <?php if (!empty($doc['hasDigitalObject'])): ?>
    <div class="search-result-preview">
      <a href="<?php echo url_for(array('module' => 'actor', 'slug' => $doc['slug'])) ?>">
        <div class="preview-container">
          <?php if (isset($doc['digitalObject']['thumbnailPath'])): ?>
            <?php echo image_tag($doc['digitalObject']['thumbnailPath'],
              array('alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $culture))), 100))) ?>
          <?php else: ?>
            <?php echo image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($doc['digitalObject']['mediaTypeId']),
              array('alt' => isset($doc['digitalObject']['digitalObjectAltText']) ? $doc['digitalObject']['digitalObjectAltText'] : truncate_text(strip_markdown(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $culture))), 100))) ?>
          <?php endif; ?>
        </div>
      </a>
    </div>
  <?php endif; ?>

  <div class="search-result-description">

    <p class="title"><?php echo link_to(render_value_inline(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $culture))), array('module' => 'actor', 'slug' => $doc['slug'])) ?></p>

    <?php echo get_component('clipboard', 'button', array('slug' => $doc['slug'], 'wide' => false, 'type' => $clipboardType)) ?>

    <ul class="result-details">

      <?php if (!empty($doc['descriptionIdentifier'])): ?>
        <li class="reference-code"><?php echo $doc['descriptionIdentifier'] ?></li>
      <?php endif; ?>

      <?php if (!empty($doc['entityTypeId']) && null !== $term = QubitTerm::getById($doc['entityTypeId'])): ?>
        <li><?php echo render_value_inline($term) ?></li>
      <?php endif; ?>

      <?php if (strlen($dates = get_search_i18n($doc, 'datesOfExistence', array('culture' => $culture))) > 0): ?>
        <li><?php echo render_value_inline($dates) ?></li>
      <?php endif; ?>

    </ul>

    <?php if (null !== $history = get_search_i18n($doc, 'history', array('culture' => $culture))): ?>
      <div class="history"><?php echo render_value($history) ?></div>
    <?php endif; ?>
  </div>

</article>
