<article class="search-result row g-0 p-3 border-bottom">
  <?php if (!empty($doc['hasDigitalObject'])) { ?>
    <div class="col-12 col-lg-3 pb-2 pb-lg-0 pe-lg-3">
      <a href="<?php echo url_for(
          ['module' => 'actor', 'slug' => $doc['slug']]
      ); ?>">
        <?php echo image_tag(
            $doc['digitalObject']['thumbnailPath']
            ?: QubitDigitalObject::getGenericIconPathByMediaTypeId(
                $doc['digitalObject']['mediaTypeId'] ?: null
            ),
            [
                'alt' => $doc['digitalObject']['digitalObjectAltText'] ?: strip_markdown(
                    get_search_i18n(
                        $doc,
                        'authorizedFormOfName',
                        ['allowEmpty' => false, 'culture' => $culture]
                    )
                ),
                'class' => 'img-thumbnail',
            ]
        ); ?>
      </a>
    </div>
  <?php } ?>

  <div class="col-12<?php echo empty($doc['hasDigitalObject']) ? '' : ' col-lg-9'; ?> d-flex flex-column gap-1">
    <div class="d-flex align-items-center gap-2 mw-100">
      <?php echo link_to(
          render_title(get_search_i18n(
              $doc,
              'authorizedFormOfName',
              ['allowEmpty' => false, 'culture' => $culture]
          )),
          ['module' => 'actor', 'slug' => $doc['slug']],
          ['class' => 'h5 mb-0 text-truncate'],
      ); ?>

      <?php echo get_component('clipboard', 'button', [
          'slug' => $doc['slug'],
          'type' => $clipboardType,
          'wide' => false,
      ]); ?>
    </div>

    <div class="d-flex flex-column gap-2">
      <div class="d-flex flex-wrap">
        <?php $showDash = false; ?>
        <?php if (!empty($doc['descriptionIdentifier'])) { ?>
          <span class="text-primary">
            <?php echo $doc['descriptionIdentifier']; ?>
          </span>
          <?php $showDash = true; ?>
        <?php } ?>

        <?php if (
            !empty($doc['entityTypeId'])
            && null !== $term = QubitTerm::getById($doc['entityTypeId'])
        ) { ?>
          <?php if ($showDash) { ?>
            <span class="text-muted mx-2"> · </span>
          <?php } ?>
          <span class="text-muted">
            <?php echo render_value_inline($term); ?>
          </span>
          <?php $showDash = true; ?>
        <?php } ?>

        <?php if (strlen($dates = get_search_i18n(
            $doc,
            'datesOfExistence',
            ['culture' => $culture])) > 0
        ) { ?>
          <?php if ($showDash) { ?>
            <span class="text-muted mx-2"> · </span>
          <?php } ?>
          <span class="text-muted">
            <?php echo render_value_inline($dates); ?>
          </span>
        <?php } ?>
      </div>

      <?php if (strlen($history = get_search_i18n(
          $doc,
          'history',
          ['culture' => $culture])) > 0
      ) { ?>
        <span class="text-block d-none">
          <?php echo render_value($history); ?>
        </span>
      <?php } ?>
    </div>
  </div>
</article>
