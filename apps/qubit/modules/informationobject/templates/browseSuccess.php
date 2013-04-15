<?php decorate_with('layout_2col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1>
    <?php echo image_tag('/images/icons-large/icon-archival.png') ?>
    <?php echo __('Browse %1% %2%', array(
      '%1%' => $pager->getNbResults(),
      '%2%' => sfConfig::get('app_ui_label_informationobject'))) ?>
  </h1>
<?php end_slot() ?>

<?php slot('sidebar') ?>
  <div id="facets">

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-levelOfDescription',
      'label' => __('Level of description'),
      'facet' => 'levelOfDescriptionId',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-repository',
      'label' => __('Institution'),
      'facet' => 'repository_id',
      'pager' => $pager,
      'filters' => $filters)) ?>

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-terms',
      'label' => __('Places'),
      'facet' => 'terms_id',
      'pager' => $pager,
      'filters' => $filters)) ?>

  </div>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <ul class="nav nav-tabs">

    <?php if ('lastUpdated' == $sortSetting): ?>
      <li<?php if ('titleDown' != $sf_request->sort && 'titleUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <li<?php if ('titleDown' == $sf_request->sort || 'titleUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'titleUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
    <?php else: ?>
      <li<?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <li<?php if ('updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'titleUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
    <?php endif; ?>

    <li class="search">
      <form method="get" action="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse')) ?>">
        <?php foreach ($sf_request->getGetParameters() as $key => $value): ?>
          <input type="hidden" name="<?php echo esc_entities($key) ?>" value="<?php echo esc_entities($value) ?>"/>
        <?php endforeach; ?>
        <div class="input-append">
          <input type="text" class="span3" name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>" placeholder="<?php echo __('Search') ?>" />
          <span class="add-on">
            <input type="submit" value="<?php echo __('Search %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))) ?>"/>
          </span>
        </div>
      </form>
    </li>

  </ul>
<?php end_slot() ?>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>
        <?php echo __('Title') ?>
        <?php if ('lastUpdated' == $sortSetting): ?>
          <?php if ('titleDown' == $sf_request->sort): ?>
            <?php echo link_to(image_tag('up.gif'), array('sort' => 'titleUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php elseif ('titleUp' == $sf_request->sort): ?>
            <?php echo link_to(image_tag('down.gif'), array('sort' => 'titleDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php endif; ?>
        <?php else: ?>
          <?php if (('titleDown' != $sf_request->sort && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort) || ('titleUp' == $sf_request->sort)): ?>
            <?php echo link_to(image_tag('down.gif'), array('sort' => 'titleDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php endif; ?>
          <?php if ('titleDown' == $sf_request->sort): ?>
            <?php echo link_to(image_tag('up.gif'), array('sort' => 'titleUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php endif; ?>
        <?php endif; ?>
      </th><th>
        <?php if ('lastUpdated' == $sortSetting): ?>
          <?php if ('titleUp' == $sf_request->sort || 'titleDown' == $sf_request->sort): ?>
            <?php echo __('Level') ?>
          <?php else: ?>
            <?php if (sfConfig::get('app_multi_repository')): ?>
              <?php echo sfConfig::get('app_ui_label_repository') ?>
            <?php else: ?>
              <?php echo __('Level') ?>
            <?php endif; ?>
          <?php endif; ?>
        <?php else: ?>
          <?php if ('updatedUp' == $sf_request->sort || 'updatedDown' == $sf_request->sort): ?>
            <?php if (sfConfig::get('app_multi_repository')): ?>
              <?php echo sfConfig::get('app_ui_label_repository') ?>
            <?php else: ?>
              <?php echo __('Level') ?>
            <?php endif; ?>
          <?php else: ?>
            <?php echo __('Level') ?>
          <?php endif; ?>
        <?php endif; ?>
      </th><th>
        <?php if ('titleDown' == $sf_request->sort || 'titleUp' == $sf_request->sort): ?>
          <?php if (sfConfig::get('app_multi_repository')): ?>
            <?php echo __(sfConfig::get('app_ui_label_repository')) ?>
          <?php else: ?>
            <?php echo __(sfConfig::get('app_ui_label_creator')) ?>
          <?php endif; ?>
        <?php else: ?>
          <?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort || 'lastUpdated' == $sortSetting): ?>
            <?php echo __('Updated') ?>
            <?php if ('updatedUp' == $sf_request->sort): ?>
              <?php echo link_to(image_tag('up.gif'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
            <?php else: ?>
              <?php echo link_to(image_tag('down.gif'), array('sort' => 'updatedUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
            <?php endif; ?>
          <?php else: ?>
            <?php if (sfConfig::get('app_multi_repository')): ?>
              <?php echo __(sfConfig::get('app_ui_label_repository')) ?>
            <?php else: ?>
              <?php echo __(sfConfig::get('app_ui_label_creator')) ?>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>

          <?php echo link_to(get_search_i18n($doc, 'title'), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?>

        </td><td>

          <?php if ('lastUpdated' == $sortSetting): ?>

            <?php if ('titleUp' == $sf_request->sort || 'titleDown' == $sf_request->sort): ?>
              <?php echo QubitTerm::getById($doc['levelOfDescriptionId'])->__toString() ?>
            <?php else: ?>
              <?php if (sfConfig::get('app_multi_repository') && isset($doc['repository'])): ?>
                <?php echo link_to(render_title(get_search_i18n($doc['repository'], 'authorizedFormOfName')), array('module' => 'repository', 'slug' => $doc['repository']['slug'])) ?>
              <?php else: ?>
                <?php echo $types[$doc['levelOfDescriptionId']] ?>
              <?php endif; ?>
            <?php endif; ?>

          <?php else: ?>

            <?php if ('updatedUp' == $sf_request->sort || 'updatedDown' == $sf_request->sort): ?>
              <?php if (sfConfig::get('app_multi_repository') && isset($doc['repository'])): ?>
                <?php echo link_to(render_title(get_search_i18n($doc['repository'], 'authorizedFormOfName')), array('module' => 'repository', 'slug' => $doc['repository']['slug'])) ?>
              <?php else: ?>
                <?php echo $types[$doc['levelOfDescriptionId']] ?>
              <?php endif; ?>
            <?php else: ?>
              <?php echo $types[$doc['levelOfDescriptionId']] ?>
            <?php endif; ?>

          <?php endif; ?>

        </td><td>

          <?php if ('titleDown' == $sf_request->sort || 'titleUp' == $sf_request->sort): ?>

            <?php if (sfConfig::get('app_multi_repository') && isset($doc['repository'])): ?>
              <?php echo link_to(render_title(get_search_i18n($doc['repository'], 'authorizedFormOfName')), array('module' => 'repository', 'slug' => $doc['repository']['slug'])) ?>
            <?php else: ?>
              <ul>
                <?php foreach ($doc['creators'] as $item): ?>
                  <li><?php echo link_to(render_title(get_search_i18n($item, 'authorizedFormOfName')), array('module' => 'actor', $item['slug'])) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

          <?php else: ?>

            <?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort || 'lastUpdated' == $sortSetting): ?>
              <?php echo format_date($doc['updatedAt'], 'f') ?>
            <?php else: ?>
              <?php if (sfConfig::get('app_multi_repository') && isset($doc['repository'])): ?>
                <?php echo link_to(render_title(get_search_i18n($doc['repository'], 'authorizedFormOfName')), array('module' => 'repository', 'slug' => $doc['repository']['slug'])) ?>
              <?php else: ?>
                <ul>
                  <?php foreach ($doc['creators'] as $item): ?>
                    <li><?php echo link_to(render_title(get_search_i18n($item, 'authorizedFormOfName')), array('module' => 'actor', $item['slug'])) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            <?php endif; ?>

          <?php endif; ?>

        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
