<?php if ($descriptions->getTotalHits() > 0): ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-archival-small.png', array('width' => '24', 'height' => '24')) ?>
    <ul>
      <?php foreach ($descriptions->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li>
          <?php echo link_to(get_search_i18n_highlight($hit, 'title.autocomplete'), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
          <strong><?php echo $levelsOfDescription[$doc['levelOfDescriptionId']] ?></strong>
        </li>
      <?php endforeach; ?>
      <?php if ($descriptions->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching descriptions'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
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
        <li><?php echo link_to(get_search_i18n_highlight($hit, 'name.autocomplete'), array('module' => 'term', 'action' => 'browseTerm', 'slug' => $hit->slug)) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
<?php endif; ?>

<?php if ($repositories->getTotalHits() > 0): ?>
  <section>
    <?php echo image_tag('/images/icons-small/icon-institutions-small.png', array('width' => '24', 'height' => '24')) ?>
    <ul>
      <?php foreach ($repositories->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n_highlight($hit, 'authorizedFormOfName.autocomplete'), array('module' => 'repository', 'slug' => $doc['slug'])) ?></li>
      <?php endforeach; ?>
      <?php if ($repositories->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching institutions'), array('module' => 'repository', 'action' => 'browse') + $sf_request->getParameterHolder()->getAll()) ?></li>
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
        <li><?php echo link_to(get_search_i18n_highlight($hit, 'authorizedFormOfName.autocomplete'), array('module' => 'search', 'action' => 'index', 'names_id' => $hit->getId())) ?></li>
      <?php endforeach; ?>
      <?php if ($actors->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching people & organizations'), array('module' => 'actor', 'action' => 'browse') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php endif; ?>
