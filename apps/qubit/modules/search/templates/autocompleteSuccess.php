<?php if ($descriptions->getTotalHits() > 0) { ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-archival-small.png', ['width' => '24', 'height' => '24', 'alt' => sfConfig::get('app_ui_label_informationobject')]); ?>
    <ul>
      <?php foreach ($descriptions->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <li>
          <?php echo link_to(render_title(get_search_i18n($doc, 'title')), ['module' => 'informationobject', 'slug' => $doc['slug']]); ?>
          <?php $lodId = $doc['levelOfDescriptionId']; ?>
          <?php if (null !== $lodId) { ?>
            <?php echo render_value_inline($levelsOfDescription[$lodId]); ?>
          <?php } ?>
        </li>
      <?php } ?>
      <?php if ($descriptions->getTotalHits() > 3) { ?>
        <li class="showall"><?php echo link_to(__('all matching descriptions'), ['module' => 'informationobject', 'action' => 'browse', 'topLod' => '0'] + $sf_data->getRaw('allMatchingIoParams')); ?></li>
      <?php } ?>
    </ul>
  </section>
<?php } ?>

<?php if ($repositories->getTotalHits() > 0) { ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-institutions-small.png', ['width' => '24', 'height' => '24', 'alt' => sfConfig::get('app_ui_label_actor')]); ?>
    <ul>
      <?php foreach ($repositories->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <li><?php echo link_to(render_title(get_search_i18n($doc, 'authorizedFormOfName')), ['module' => 'repository', 'slug' => $doc['slug']]); ?></li>
      <?php } ?>
      <?php if ($repositories->getTotalHits() > 3) { ?>
        <li class="showall"><?php echo link_to(__('all matching institutions'), ['module' => 'repository', 'action' => 'browse'] + $sf_data->getRaw('allMatchingParams')); ?></li>
      <?php } ?>
    </ul>
  </section>
<?php } ?>

<?php if ($actors->getTotalHits() > 0) { ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-people-small.png', ['width' => '24', 'height' => '24', 'alt' => sfConfig::get('app_ui_label_repository')]); ?>
    <ul>
      <?php foreach ($actors->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <li><?php echo link_to(render_title(get_search_i18n($doc, 'authorizedFormOfName')), ['module' => 'actor', 'slug' => $doc['slug']]); ?></li>
      <?php } ?>
      <?php if ($actors->getTotalHits() > 3) { ?>
        <li class="showall"><?php echo link_to(__('all matching people & organizations'), ['module' => 'actor', 'action' => 'browse'] + $sf_data->getRaw('allMatchingParams')); ?></li>
      <?php } ?>
    </ul>
  </section>
<?php } ?>

<?php if ($places->getTotalHits() > 0) { ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-places-small.png', ['width' => '24', 'height' => '24', 'alt' => sfConfig::get('app_ui_label_place')]); ?>
    <ul>
      <?php foreach ($places->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <li><?php echo link_to(render_title(get_search_i18n($doc, 'name')), ['module' => 'term', 'slug' => $doc['slug']]); ?></li>
      <?php } ?>
      <?php if ($places->getTotalHits() > 3) { ?>
        <li class="showall"><?php echo link_to(__('all matching places'), ['module' => 'taxonomy', 'action' => 'index', 'slug' => 'places', 'subqueryField' => 'allLabels'] + $sf_data->getRaw('allMatchingParams')); ?></li>
      <?php } ?>
    </ul>
  </section>
<?php } ?>

<?php if ($subjects->getTotalHits() > 0) { ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-subjects-small.png', ['width' => '24', 'height' => '24', 'alt' => sfConfig::get('app_ui_label_subject')]); ?>
    <ul>
      <?php foreach ($subjects->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <li><?php echo link_to(render_title(get_search_i18n($doc, 'name')), ['module' => 'term', 'slug' => $doc['slug']]); ?></li>
      <?php } ?>
      <?php if ($subjects->getTotalHits() > 3) { ?>
        <li class="showall"><?php echo link_to(__('all matching subjects'), ['module' => 'taxonomy', 'action' => 'index', 'slug' => 'subjects', 'subqueryField' => 'allLabels'] + $sf_data->getRaw('allMatchingParams')); ?></li>
      <?php } ?>
    </ul>
  </section>
<?php } ?>
