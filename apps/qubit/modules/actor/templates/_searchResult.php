<?php use_helper('Text') ?>

<article class="search-result">

  <div class="search-result-description">

    <p class="title"><?php echo link_to(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false, 'culture' => $culture)), array('module' => 'actor', 'slug' => $doc['slug'])) ?></p>

    <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => false)) ?>

    <ul class="result-details">

      <?php if (!empty($doc['descriptionIdentifier'])): ?>
        <li class="reference-code"><?php echo $doc['descriptionIdentifier'] ?></li>
      <?php endif; ?>

      <?php if (!empty($types[$doc['entityTypeId']])): ?>
        <li><?php echo $types[$doc['entityTypeId']] ?></li>
      <?php endif; ?>

      <li><?php echo get_search_i18n($doc, 'datesOfExistence', array('culture' => $culture)) ?></li>

    </ul>

  </div>

</article>
