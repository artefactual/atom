<?php
$doc = $hit->getData();

// The highlights contain a mapping from search index field name to an array of fragments
// containing the parts of that field that matched the query in the search index. If a field did
// not return results for the query, or higlighting is disabled, the key for that field will not
// exist in this array.
$highlights = reset($hit->getHighlights());
$titleHighlight = $highlights["i18n.{$culture}.title"][0] ?? null;
$scopeHighlight = $highlights["i18n.{$culture}.scopeAndContent"][0] ?? null;
$creatorHighlight = $highlights["creators.i18n.{$culture}.authorizedFormOfName"][0] ?? null;
$refCodeHighlight = $highlights['referenceCode'][0] ?? null;
$identifierHighlight = $highlights['identifier'][0] ?? null;

// We can render other highlights, but we want to ensure that they're not already rendered in the
// search result.
$highlightsRenderedElsewhere = [
    "i18n.{$culture}.title",
    "i18n.{$culture}.scopeAndContent",
    "creators.i18n.{$culture}.authorizedFormOfName",
    'referenceCode',
    'identifier',
];

$otherHighlights = array_diff_key($highlights, array_flip($highlightsRenderedElsewhere));

$maxFragmentSize = 150;
?>

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
          render_title_with_highlights(get_search_i18n(
              $doc,
              'title',
              ['allowEmpty' => false, 'culture' => $culture, 'highlight' => $titleHighlight],
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
            <span class="text-primary">
              <?php
              $refCode = null !== $refCodeHighlight ? render_value_with_highlights($refCodeHighlight) : $doc['referenceCode'];
              echo $refCode;
              ?>
            </span>
            <?php $showDash = true; ?>
          <?php } elseif (isset($doc['identifier']) && !empty($doc['identifier'])) { ?>
            <span class="text-primary">
              <?php
              $identifier = null !== $identifierHighlight ? render_value_with_highlights($identifierHighlight) : $doc['identifier'];
              echo $identifier;
              ?>
            </span>
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
          ['culture' => $culture, 'highlight' => $scopeHighlight],
      )) { ?>
        <span class="text-block d-none">
          <?php echo render_value_with_highlights($scopeAndContent); ?>
        </span>
      <?php } ?>

      <?php if (
          isset($doc['creators'])
          && null !== $creationDetails = get_search_creation_details($doc, ['allowEmpty' => false, 'culture' => $culture, 'cultureFallback' => true, 'highlight' => $creatorHighlight])
      ) { ?>
        <span class="text-muted">
          <?php echo render_value_with_highlights($creationDetails); ?>
        </span>
      <?php } ?>

      <?php if (!empty($otherHighlights)) { ?>
        <?php
        $firstHighlightText = current($otherHighlights)[0];
        $highlightFieldKey = array_key_first($otherHighlights);
        $ellipsize = strlen($firstHighlightText) >= $maxFragmentSize;
        ?>
        <div class="search-highlight-other d-print-none">
          <div class="text-block highlight-summary summary">
            <span>
              <i class="fas fa-search" aria-hidden="true"></i>
              &nbsp;
              <?php if ('transcript' === $highlightFieldKey) {
              echo __('Search matched digital object transcript:');
              } else {
              echo __('Search matched:');
              } ?>
            </span>
            <span class="search-highlight-fragment">
              <?php if ($ellipsize) {
              echo '&hellip;';
              } ?>
              <?php echo render_value_with_highlights($firstHighlightText); ?>
              <?php if ($ellipsize) {
              echo '&hellip;';
              } ?>
            </span>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</article>
