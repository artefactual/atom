<?php
// This template expects:
// - hit (the Elasticsearch hit data)
// - highlights (the highlight array from Elasticsearch)

// Render other highlights that are not shown in the searchResult template. Ignore:
// - Identifiers
// - The language filter
// - Scope and content, title, creators in other languages
$skippedFieldPatterns = [
    '/^(?:referenceCode|identifier)$/',
    '/^i18n\.languages$/',
    '/^i18n\.[^.]+\.title$/',
    '/^i18n\.[^.]+\.scopeAndContent$/',
    '/^creators\.i18n\.[^.]+\.authorizedFormOfName$/',
];

$otherHighlights = [];

foreach ($highlights->getRawValue() as $key => $value) {
  foreach ($skippedFieldPatterns as $pattern) {
    if (!preg_match($pattern, $key)) {
      $otherHighlights[$key] = $value;
    }
  }
}

if (empty($otherHighlights)) {
    return;
}

$maxFragmentSize = intval(sfConfig::get('app_highlight_search_fragment_size', 150));
$firstHighlightText = current($otherHighlights)[0];
$firstHighlightKey = array_key_first($otherHighlights);
$ellipsize = strlen($firstHighlightText) >= $maxFragmentSize;
$numHighlightsHidden = count($otherHighlights) - 1;
$additionalHighlightsId = 'search-highlight-additional-'.$hit->getId();
?>

<div class="search-highlight-other d-print-none">
  <div class="highlight-summary">
    <span>
      <i class="fas fa-search" aria-hidden="true"></i>
      &nbsp;
      <?php if ('transcript' === $firstHighlightKey) {
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
    <?php if ($numHighlightsHidden > 0) { ?>
      <button
        class="search-highlight-count btn btn-link collapsed"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#<?php echo $additionalHighlightsId; ?>"
        aria-expanded="false"
        aria-controls="<?php echo $additionalHighlightsId; ?>">
        <?php if (1 === $numHighlightsHidden) { ?>
          <?php echo __('Search also matched 1 other field'); ?>
        <?php } else { ?>
          <?php echo __('Search also matched %1% other fields', ['%1%' => $numHighlightsHidden]); ?>
        <?php } ?>
        <i class="fas fa-chevron-down ms-1" aria-hidden="true"></i>
      </button>
      <ul id="<?php echo $additionalHighlightsId; ?>" class="search-highlight-additional collapse mb-0">
        <?php foreach (array_slice($otherHighlights, 1) as $highlightTexts) { ?>
          <?php
          $highlightText = $highlightTexts[0];
          $ellipsize = strlen($highlightText) >= $maxFragmentSize;
          ?>
          <li class="search-highlight-fragment">
            <?php if ($ellipsize) {
            echo '&hellip;';
            } ?>
            <?php echo render_value_with_highlights($highlightText); ?>
            <?php if ($ellipsize) {
            echo '&hellip;';
            } ?>
          </li>
        <?php } ?>
      </ul>
    <?php } ?>
  </div>
</div>
