<?php if ($descriptions->getTotalHits() > 0): ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-archival-small.png', array('width' => '24', 'height' => '24')) ?>
    <ul>
      <?php foreach ($descriptions->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li>
          <?php echo link_to(get_search_i18n($hit, 'title', array('flat' => true)), array('module' => 'informationobject', 'slug' => $doc->get('slug')->get(0))) ?>
          <?php $lodId = $doc->get('levelOfDescriptionId') ?>
          <?php if (null !== $lodId): ?>
            <?php echo $levelsOfDescription->get($lodId->get(0)) ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
      <?php if ($descriptions->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching descriptions'), array('module' => 'search', 'action' => 'index') + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php endif; ?>

<?php if ($repositories->getTotalHits() > 0): ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-institutions-small.png', array('width' => '24', 'height' => '24')) ?>
    <ul>
      <?php foreach ($repositories->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n($hit, 'authorizedFormOfName', array('flat' => true)), array('module' => 'repository', 'slug' => $doc->get('slug')->get(0))) ?></li>
      <?php endforeach; ?>
      <?php if ($repositories->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching institutions'), array('module' => 'repository', 'action' => 'browse') + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php endif; ?>

<?php if ($actors->getTotalHits() > 0): ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-people-small.png', array('width' => '24', 'height' => '24')) ?>
    <ul>
      <?php foreach ($actors->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n($hit, 'authorizedFormOfName', array('flat' => true)), array('module' => 'actor', 'slug' => $doc->get('slug')->get(0))) ?></li>
      <?php endforeach; ?>
      <?php if ($actors->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching people & organizations'), array('module' => 'actor', 'action' => 'browse') + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php endif; ?>

<?php if ($places->getTotalHits() > 0): ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-places-small.png', array('width' => '24', 'height' => '24')) ?>
    <ul>
      <?php foreach ($places->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n($hit, 'name', array('flat' => true)), array('module' => 'term', 'slug' => $doc->get('slug')->get(0))) ?></li>
      <?php endforeach; ?>
      <?php if ($places->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all places'), array('module' => 'taxonomy', 'slug' => 'places')) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php endif; ?>

<?php if ($subjects->getTotalHits() > 0): ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-subjects-small.png', array('width' => '24', 'height' => '24')) ?>
    <ul>
      <?php foreach ($subjects->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n($hit, 'name', array('flat' => true)), array('module' => 'term', 'slug' => $doc->get('slug')->get(0))) ?></li>
      <?php endforeach; ?>
      <?php if ($subjects->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all subjects'), array('module' => 'taxonomy', 'slug' => 'subjects')) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php endif; ?>
