<?php use_helper('Date'); ?>

<article class="search-result row g-0 p-3 border-bottom">
  <div class="col-12 d-flex flex-column gap-1"">
    <div class="d-flex align-items-center gap-2 mw-100">
      <?php echo link_to(
          render_title(get_search_i18n(
              $doc,
              'title',
              ['allowEmpty' => false, 'culture' => $culture]
          )),
          ['module' => 'accession', 'slug' => $doc['slug']],
          ['class' => 'h5 mb-0 text-truncate'],
      ); ?>

      <?php if ($canExportAccessions) { ?>
        <?php echo get_component('clipboard', 'button', [
            'slug' => $doc['slug'],
            'type' => $clipboardType,
            'wide' => false,
        ]); ?>
      <?php } ?>
    </div>

    <div class="d-flex flex-column gap-2">
      <div class="d-flex flex-wrap">
        <?php $showDash = false; ?>
        <?php if (!empty($doc['identifier'])) { ?>
          <span class="text-primary">
            <?php echo $doc['identifier']; ?>
          </span>
          <?php $showDash = true; ?>
        <?php } ?>

        <?php if (!empty($doc['date'])) { ?>
          <?php if ($showDash) { ?>
            <span class="text-muted mx-2"> · </span>
          <?php } ?>
          <span class="text-muted">
            <?php echo format_date($doc['date'], 'i'); ?>
          </span>
          <?php $showDash = true; ?>
        <?php } ?>

        <?php if (!empty($doc['updatedAt'])) { ?>
          <?php if ($showDash) { ?>
            <span class="text-muted mx-2"> · </span>
          <?php } ?>
          <span class="text-muted">
            <?php echo sprintf('%s: %s', __('Last updated'), format_date($doc['updatedAt'], 'f')); ?>
          </span>
        <?php } ?>
      </div>

      <?php if (null !== $scopeAndContent = get_search_i18n($doc, 'scopeAndContent', ['culture' => $culture])) { ?>
        <span class="text-block d-none">
          <?php echo render_value($scopeAndContent); ?>
        </span>
      <?php } ?>
    </div>
  </div>
</article>
