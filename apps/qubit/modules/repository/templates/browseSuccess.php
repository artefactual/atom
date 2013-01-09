<?php use_helper('Date') ?>

<div id="search-results">

  <div class="row">

    <div class="hidden-phone">
      <div class="span8">
        <h1>
          <?php echo image_tag('/plugins/arDominionPlugin/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')) ?>
          <?php echo __('Browse %1% institutions', array('%1%' => $pager->getNbResults(), '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
        </h1>
      </div>
      <div class="span4">
        <div class="btn-group top-options">
          <?php echo link_to(
            __('Alphabetic'),
            array('sort' => 'alphabetic') + $sf_request->getParameterHolder()->getAll(),
            array('class' => 'btn' . ('alphabetic' == $sortSetting ? ' active' : ''))) ?>
          <?php echo link_to(
            __('Last updated'),
            array('sort' => 'lastUpdated') + $sf_request->getParameterHolder()->getAll(),
            array('class' => 'btn' . ('lastUpdated' == $sortSetting ? ' active' : ''))) ?>
        </div>
      </div>
    </div>

    <div id="filter" class="span12 visible-phone">
      <h2 class="widebtn btn-huge" data-toggle="collapse" data-target="#facets">
        <?php echo __('Filter %1% institutions', array('%1%' => $pager->getNbResults(), '%2%' => sfConfig::get('app_ui_label_repository'))) ?>
      </h2>
    </div>

  </div>

  <div class="row">

    <div class="span3" id="facets">

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-archivetype',
        'label' => __('Archive type'),
        'facet' => 'types',
        'pager' => $pager,
        'filters' => $filters)) ?>

      <?php echo get_partial('search/facet', array(
        'target' => '#facet-province',
        'label' => __('Region'),
        'facet' => 'contact_i18n_region',
        'pager' => $pager,
        'filters' => $filters)) ?>

    </div>

    <div class="span9">

      <div class="section masonry">

        <?php foreach ($pager->getResults() as $hit): ?>
          <?php $doc = $hit->getData() ?>
          <div class="brick brick-small">
            <div class="preview">
              <a href="<?php echo url_for(array('module' => 'repository', 'slug' => $doc['slug'])) ?>">
                <?php if (file_exists(sfConfig::get('sf_upload_dir').'/r/'.$doc['slug'].'/conf/logo.png')): ?>
                  <?php echo image_tag('/uploads/r/'.$doc['slug'].'/conf/logo.png') ?>
                <?php else: ?>
                  <h4><?php echo get_search_i18n($doc, 'authorizedFormOfName') ?></h4>
                <?php endif; ?>
              </a>
            </div>
            <div class="details">
              <?php if (isset($doc['actor'][$sf_user->getCulture()]['authorizedFormOfName'])): ?>
                <p><span class="name"><?php echo get_search_i18n($doc, 'authorizedFormOfName') ?></span></p>
              <?php endif; ?>
              <?php if ('lastUpdated' == $sortSetting): ?>
                <p><span class="date"><?php echo format_date($doc['updatedAt'], 'f') ?></span></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <div class="section">

        <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

      </div>

    </div>

  </div>

</div>
