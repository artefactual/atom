<?php if ($descriptions->getTotalHits() > 0): ?>
  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-archival-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>
      <?php foreach ($descriptions->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li>
          <?php echo link_to(get_search_i18n($doc, 'title'), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
          <strong><?php echo $levelsOfDescription[$doc['levelOfDescriptionId']] ?></strong>
        </li>
      <?php endforeach; ?>
      <?php if ($descriptions->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching descriptions'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($subjects->getTotalHits() > 0): ?>
  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-subjects-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>
      <?php foreach ($subjects->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n($doc, 'name'), array('module' => 'search', 'action' => 'index', 'subjects_id' => $hit->getId())) ?></li>
      <?php endforeach; ?>
      <?php if ($subjects->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching subjects'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($repositories->getTotalHits() > 0): ?>
  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-institutions-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>
      <?php foreach ($repositories->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n($doc, 'authorizedFormOfName'), array('module' => 'repository', 'slug' => $doc['slug'])) ?></li>
      <?php endforeach; ?>
      <?php if ($repositories->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching institutions'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($actors->getTotalHits() > 0): ?>
  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-people-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>
      <?php foreach ($actors->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <li><?php echo link_to(get_search_i18n($doc, 'authorizedFormOfName'), array('module' => 'search', 'action' => 'index', 'names_id' => $hit->getId())) ?></li>
      <?php endforeach; ?>
      <?php if ($actors->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching people & organizations'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

<div id="search-bottom">
  <?php echo link_to(__('Advanced search'), array('module' => 'search', 'action' => 'advanced')) ?>
</div>
