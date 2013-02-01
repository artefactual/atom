<?php use_helper('Text') ?>

<article class="search-result">

  <?php if (isset($doc['hasDigitalObject'])): ?>

    <div class="search-result-preview">

      <?php if (isset($doc['digitalObject']['thumbnailPath'])): ?>
        <?php echo link_to(
          image_tag(
            $doc['digitalObject']['thumbnailPath'],
            array('alt' => 'image-thumb', 'width' => '150', 'height' => '150')),
          array('module' => 'informationobject', 'slug' => $doc['slug']),
          array('title' => get_search_i18n($doc, 'title'))) ?>
      <?php endif; ?>

    </div>

  <?php endif; ?>

  <div class="search-result-description">

    <!-- TODO: show level of description -->
    <!-- TODO: show publication status -->
    <p class="title"><?php echo link_to(render_title(get_search_i18n($doc, 'title')), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></p>

    <ul class="result-details">

      <?php if (isset($doc['referenceCode'])): ?>
        <li class="reference-code"><?php echo $doc['referenceCode'] ?></li>
      <?php endif; ?>

      <?php if (isset($doc['levelOfDescriptionId'])): ?>
        <li class="level-description"><?php echo $doc['levelOfDescriptionId'] ?></li>
      <?php endif; ?>

      <li class="publication-status">Draft</li>

      <!-- TODO: show dates -->
      <?php if (false): ?>
      <li class="dates">
        <?php if (isset($doc['dates'])): ?>
          <?php echo Qubit::renderDateStartEnd(null, $doc['dates'][0]['startDate'], $doc['dates'][0]['endDate']) ?>
        <?php endif; ?>
        <?php if (isset($doc['creators'][$sf_user->getCulture()])): ?>
          <?php echo __('by %1%', array('%1%' => $doc['creators'][$sf_user->getCulture()]['i18n'][0]['authorizedFormOfName'])) ?>
        <?php endif; ?>
      </li>
      <?php endif; ?>

    </ul>

    <!-- TODO: show repo if multirepo -->
    <ul class="breadcrumb">
      <?php foreach($doc['ancestors'] as $id): ?>
        <?php if ($id == QubitInformationObject::ROOT_ID) continue ?>
        <li><?php echo link_to($pager->ancestors[$id]['title'], array('module' => 'informationobject', 'slug' => $pager->ancestors[$id]['slug']), array('title' => $pager->ancestors[$id]['title'])) ?></li>
      <?php endforeach; ?>
    </ul>

    <?php if (null !== $scopeAndContent = get_search_i18n($doc, 'scopeAndContent')): ?>
      <p><?php echo truncate_text($scopeAndContent, 250) ?></p>
    <?php endif; ?>

  </div>

</article>
