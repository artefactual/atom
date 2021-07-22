<?php $doc = $hit->getData(); ?>

<article class="search-result row g-0 p-3 border-bottom">
  <?php if (!empty($doc['hasDigitalObject'])) { ?>
    <?php
        // Get thumbnail or generic icon path
        if (
            isset($doc['digitalObject']['thumbnailPath'])
            && QubitAcl::check(
                QubitInformationObject::getById($hit->getId()),
                'readThumbnail'
            )
        ) {
            $imagePath = $doc['digitalObject']['thumbnailPath'];
        } else {
            $imagePath = QubitDigitalObject::getGenericIconPathByMediaTypeId(
                $doc['digitalObject']['mediaTypeId'] ?: null
            );
        }
    ?>
    <div class="col-12 col-lg-3 pb-2 pb-lg-0 pe-lg-3">
      <a href="<?php echo url_for(
          ['module' => 'informationobject', 'slug' => $doc['slug']]
      ); ?>">
        <?php echo image_tag($imagePath, [
            'alt' => $doc['digitalObject']['digitalObjectAltText'] ?: strip_markdown(
                get_search_i18n(
                    $doc,
                    'title',
                    ['allowEmpty' => false, 'culture' => $culture]
                )
            ),
            'class' => 'img-thumbnail',
        ]); ?>
      </a>
    </div>
  <?php } ?>

  <div class="col-12<?php echo empty($doc['hasDigitalObject']) ? '' : ' col-lg-9'; ?> d-flex flex-column gap-1">
    <div class="d-flex align-items-center gap-2">
      <?php echo link_to(
          render_title(get_search_i18n(
              $doc,
              'title',
              ['allowEmpty' => false, 'culture' => $culture]
          )),
          ['module' => 'informationobject', 'slug' => $doc['slug']],
          ['class' => 'h5 mb-0 text-truncate'],
      ); ?>

      <?php echo get_component('clipboard', 'button', [
          'slug' => $doc['slug'],
          'type' => 'informationObject',
          'wide' => false,
      ]); ?>
    </div>

    <div class="d-flex flex-column gap-2">
      <div class="d-flex flex-column">
        <div class="d-flex flex-wrap">
          <?php $showDash = false; ?>
          <?php if (
              '1' == sfConfig::get('app_inherit_code_informationobject', 1)
              && isset($doc['referenceCode']) && !empty($doc['referenceCode'])
          ) { ?>
            <span class="text-primary"><?php echo $doc['referenceCode']; ?></span>
            <?php $showDash = true; ?>
          <?php } elseif (isset($doc['identifier']) && !empty($doc['identifier'])) { ?>
            <span class="text-primary"><?php echo $doc['identifier']; ?></span>
            <?php $showDash = true; ?>
          <?php } ?>

          <?php if (
              isset($doc['levelOfDescriptionId'])
              && !empty($doc['levelOfDescriptionId'])
          ) { ?>
            <?php if ($showDash) { ?>
              <span class="text-muted mx-2"> · </span>
            <?php } ?>
            <span class="text-muted">
              <?php echo render_value_inline(
                  QubitCache::getLabel($doc['levelOfDescriptionId'], 'QubitTerm')
              ); ?>
            </span>
            <?php $showDash = true; ?>
          <?php } ?>

          <?php if (isset($doc['dates'])) { ?>
            <?php $date = render_search_result_date($doc['dates']); ?>
            <?php if (!empty($date)) { ?>
              <?php if ($showDash) { ?>
                <span class="text-muted mx-2"> · </span>
              <?php } ?>
              <span class="text-muted">
                <?php echo render_value_inline($date); ?>
              </span>
              <?php $showDash = true; ?>
            <?php } ?>
          <?php } ?>

          <?php if (
              isset($doc['publicationStatusId'])
              && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $doc['publicationStatusId']
          ) { ?>
            <?php if ($showDash) { ?>
              <span class="text-muted mx-2"> · </span>
            <?php } ?>
            <span class="text-muted">
              <?php echo render_value_inline(
                  QubitCache::getLabel($doc['publicationStatusId'], 'QubitTerm')
              ); ?>
            </span>
          <?php } ?>
        </div>

        <?php if (isset($doc['partOf'])) { ?>
          <span class="text-muted">
            <?php echo __('Part of '); ?>
            <?php echo link_to(
                render_title(get_search_i18n(
                    $doc['partOf'],
                    'title',
                    ['allowEmpty' => false, 'culture' => $culture, 'cultureFallback' => true]
                )),
                ['slug' => $doc['partOf']['slug'], 'module' => 'informationobject']
            ); ?>
          </span> 
        <?php } ?>
      </div>

      <?php if (null !== $scopeAndContent = get_search_i18n(
          $doc,
          'scopeAndContent',
          ['culture' => $culture]
      )) { ?>
        <span class="text-block d-none">
          <?php echo render_value($scopeAndContent); ?>
        </span>
      <?php } ?>

      <?php if (
          isset($doc['creators'])
          && null !== $creationDetails = get_search_creation_details($doc, $culture)
      ) { ?>
        <span class="text-muted">
          <?php echo render_value_inline($creationDetails); ?>
        </span>
      <?php } ?>
    </div>
  </div>
</article>
