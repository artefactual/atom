<?php use_helper('Text') ?>

<article class="search-result">

  <div class="search-result-description">

    <p class="title"><?php echo link_to(get_search_i18n($doc, 'authorizedFormOfName'), array('module' => 'actor', 'slug' => $doc['slug'])) ?></p>

    <ul class="result-details">

      <?php if (isset($doc['entityTypeId']) && isset($types[$doc['entityTypeId']])): ?>
        <li><?php echo $types[$doc['entityTypeId']] ?></li>
      <?php endif; ?>

      <li><?php echo format_date($doc['updatedAt'], 'f') ?></li>

      <li><?php echo get_search_i18n($doc, 'datesOfExistence') ?></li>

    </ul>

  </div>

</article>
