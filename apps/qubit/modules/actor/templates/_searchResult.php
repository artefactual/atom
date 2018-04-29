<?php use_helper('Text') ?>

<article class="search-result">

  <div class="search-result-description">

    <p class="title"><?php echo link_to(render_value_inline(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $culture))), array('module' => 'actor', 'slug' => $doc['slug'])) ?></p>

    <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => false)) ?>

    <ul class="result-details">

      <?php if (!empty($doc['descriptionIdentifier'])): ?>
        <li class="reference-code"><?php echo render_value_inline($doc['descriptionIdentifier']) ?></li>
      <?php endif; ?>

      <?php if (!empty($doc['entityTypeId']) && null !== $term = QubitTerm::getById($doc['entityTypeId'])): ?>
        <li><?php echo render_title($term) ?></li>
      <?php endif; ?>

      <?php if (strlen($dates = get_search_i18n($doc, 'datesOfExistence', array('culture' => $culture))) > 0): ?>
        <li><?php echo render_value_inline($dates) ?></li>
      <?php endif; ?>

    </ul>

  </div>

</article>
