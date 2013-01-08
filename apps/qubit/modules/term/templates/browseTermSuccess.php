<?php use_helper('Text') ?>

<div id="search-results">

  <div class="row">

    <div class="span12">
      <h1>
        <?php if (isset($icon)): ?>
          <?php echo image_tag('/plugins/arDominionPlugin/images/icons-large/icon-'.$icon.'.png', array('width' => '42', 'height' => '42')) ?>
        <?php endif; ?>
        <?php echo render_title($resource->taxonomy) ?> - <?php echo render_title($resource) ?>
        <strong class="hidden-phone">
          <?php echo __('%1% search results', array('%1%' => $pager->getNbResults())) ?>
          <?php if (sfConfig::get('app_multi_repository')): ?>
            <?php echo __('in %1% institutions', array('%1%' => count($pager->facets['repository_id']['terms']))) ?>
          <?php endif; ?>
        </strong>
      </h1>
    </div>

    <div id="phone-filter" class="span12 visible-phone">
      <h2 class="widebtn btn-huge" data-toggle="collapse" data-target="#facets, #top-facet"><?php echo __('Filter %1% Results', array(
        '%1%' => $pager->getNbResults())) ?></h2>
    </div>

  </div>

  <div class="row">

    <div class="span3" id="facets">

      <?php if (isset($pager->facets['digitalObject_mediaTypeId'])): ?>
        <div class="section">
          <h2 class="visible-phone widebtn btn-huge" data-toggle="collapse" data-target="#mediatypes"><?php echo __('Media Type') ?></h2>
          <h2 class="hidden-phone"><?php echo __('Media Type') ?></h2>
          <div class="scrollable" id="mediatypes">
            <ul>
              <li <?php if ('' == $sf_request->getParameter('digitalObject_mediaTypeId')) echo 'class="active"' ?>><?php echo link_to(__('All'), array('digitalObject_mediaTypeId' => null, 'page' => null) + $sf_request->getParameterHolder()->getAll()) ?></li>
              <?php foreach($pager->facets['digitalObject_mediaTypeId']['terms'] as $id => $term): ?>
                <li <?php if (in_array($id, @$filters['digitalObject_mediaTypeId'])) echo 'class="active"' ?>><?php echo link_to(__($term['term']).'<span>'.$term['count'].'</span>', array('digitalObject_mediaTypeId' => (@$filters['digitalObject_mediaTypeId'] ? implode(',', array_diff(array_merge(@$filters['digitalObject_mediaTypeId'], array($id)), array_intersect(@$filters['digitalObject_mediaTypeId'], array($id)))) : $id), 'page' => null) + $sf_request->getParameterHolder()->getAll()) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>

      <div class="section">

        <h2 class="visible-phone widebtn btn-huge" data-toggle="collapse" data-target="#dates"><?php echo __('Creation date') ?></h2>
        <h2 class="hidden-phone"><?php echo __('Creation date') ?></h2>

        <div class="scrollable dates" id="dates">
          <input type="text" value="<?php echo $pager->facets['dates_startDate']['min'] ?>" name="from" /> - <input type="text" value="<?php echo $pager->facets['dates_startDate']['max'] ?>" name="to" />
        </div>

      </div>

      <hr />

      <?php echo link_to(__('Show all %1%', array('%1%' => $resource->taxonomy->__toString())),
        array('module' => 'taxonomy', 'action' => 'browse', 'id' => $resource->taxonomyId),
        array('class' => 'widebtn')) // HACK Use id deliberately because "Subjects" and "Places" menus still use id ?>

    </div>

    <div class="span9" id="content">

      <div class="listings">

        <?php foreach ($pager->getResults() as $item): ?>

          <?php $doc = build_i18n_doc($item, array('creators')) ?>
          <?php echo include_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>

        <?php endforeach; ?>

        <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

      </div>

    </div>

  </div>

</div>

