
<?php if ($descriptionsHits > 0): ?>

  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-archival-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>

      <?php foreach ($descriptions->getResults() as $hit): ?>
        <?php $doc = build_i18n_doc($hit) ?>
        <li>
          <?php echo link_to(($doc[$sf_user->getCulture()]['title'] ?: $doc[$doc['sourceCulture']]['title']), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>
          <strong><?php echo $levelsOfDescription[$doc['levelOfDescriptionId']] ?></strong>
        </li>
      <?php endforeach; ?>

      <?php if ($descriptions->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching descriptions'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>

    </ul>
  </div>

<?php endif; ?>

<?php if ($subjectsHits > 0): ?>

  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-subjects-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>

      <?php foreach ($subjects->getResults() as $hit): ?>
        <?php $doc = build_i18n_doc($hit) ?>
        <li><?php echo link_to(($doc[$sf_user->getCulture()]['name'] ?: $doc[$doc['sourceCulture']]['name']), array('module' => 'search', 'action' => 'index', 'subjects_id' => $hit->getId())) ?></li>
      <?php endforeach; ?>

      <?php if ($subjects->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching subjects'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>

    </ul>
  </div>

<?php endif; ?>

<?php if ($repositoriesHits > 0): ?>

  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-institutions-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>

      <?php foreach ($repositories->getResults() as $hit): ?>
        <?php $doc = build_i18n_doc($hit, array('actor')) ?>
        <li><?php echo link_to($doc['actor'][$sf_user->getCulture()]['authorizedFormOfName'], array('module' => 'repository', 'slug' => $doc['slug'])) ?></li>
      <?php endforeach; ?>

      <?php if ($repositories->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching institutions'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>

    </ul>
  </div>

<?php endif; ?>

<?php if ($actorsHits > 0): ?>

  <div>
    <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-small/icon-people-small.png', array('width' => '32', 'height' => '32')) ?>
    <ul>

      <?php foreach ($actors->getResults() as $hit): ?>
        <?php $doc = build_i18n_doc($hit) ?>
        <li><?php echo link_to(($doc[$sf_user->getCulture()]['authorizedFormOfName'] ?: $doc[$doc['sourceCulture']]['authorizedFormOfName']), array('module' => 'search', 'action' => 'index', 'names_id' => $hit->getId())) ?></li>
      <?php endforeach; ?>

      <?php if ($actors->getTotalHits() > 3): ?>
        <li class="showall"><?php echo link_to(__('all matching people & organizations'), array('module' => 'search', 'action' => 'index') + $sf_request->getParameterHolder()->getAll()) ?></li>
      <?php endif; ?>

    </ul>
  </div>

<?php endif; ?>

<div>
  <?php echo link_to(__('Advanced search'), array('module' => 'search', 'action' => 'advanced')) ?>
</div>
