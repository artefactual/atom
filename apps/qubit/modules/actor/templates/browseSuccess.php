<?php use_helper('Date') ?>

<div class="row">

  <div class="span12">
    <h1>
      <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-people.png', array('width' => '42', 'height' => '42')) ?>
      <?php echo __('Browse %1% %2%', array(
        '%1%' => $pager->getNbResults(),
        '%2%' => sfConfig::get('app_ui_label_actor'))) ?>
    </h1>
  </div>

</div>

<div class="row">

  <div class="span3" id="facets">

    <?php echo get_partial('search/facet', array(
      'target' => '#facet-type',
      'label' => __('Type'),
      'facet' => 'entityTypeId',
      'pager' => $pager,
      'filters' => $filters)) ?>

  </div>

  <div class="span9" id="main-column">

    <ul class="nav nav-tabs">

      <?php if ('lastUpdated' == $sortSetting): ?>
        <li<?php if ('nameDown' != $sf_request->sort && 'nameUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
        <li<?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <?php else: ?>
        <li<?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
        <li<?php if ('updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <?php endif; ?>

      <li class="search">
        <div class="search">
          <form method="get" action="<?php echo url_for(array('module' => 'actor', 'action' => 'browse')) ?>">
            <?php foreach ($sf_request->getGetParameters() as $key => $value): ?>
              <input type="hidden" name="<?php echo esc_entities($key) ?>" value="<?php echo esc_entities($value) ?>"/>
            <?php endforeach; ?>
            <input class="subquery" name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>" placeholder="<?php echo __('Search %1%', array('%1%' => sfConfig::get('app_ui_label_actor'))) ?>"/>
            <input class="form-submit" type="submit" value="<?php echo __('Search %1%', array('%1%' => sfConfig::get('app_ui_label_actor'))) ?>"/>
          </form>
        </div>
      </li>

    </ul>

    <div id="content">

      <div class="section">

        <table class="table table-bordered sticky-enabled">
          <thead>
            <tr>
              <th>
                <?php echo __('Name') ?>
                <?php if ('lastUpdated' == $sortSetting): ?>
                  <?php if ('nameDown' == $sf_request->sort): ?>
                    <?php echo link_to(image_tag('up.gif'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
                  <?php elseif ('nameUp' == $sf_request->sort): ?>
                    <?php echo link_to(image_tag('down.gif'), array('sort' => 'nameDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
                  <?php endif; ?>
                <?php else: ?>
                  <?php if (('nameDown' != $sf_request->sort && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort) || ('nameUp' == $sf_request->sort)): ?>
                    <?php echo link_to(image_tag('down.gif'), array('sort' => 'nameDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
                  <?php endif; ?>
                  <?php if ('nameDown' == $sf_request->sort): ?>
                    <?php echo link_to(image_tag('up.gif'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
                  <?php endif; ?>
                <?php endif; ?>
              </th>
              <?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort || ('lastUpdated' != $sortSetting && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort) ): ?>
                <th><?php echo __('Type') ?></th>
              <?php else: ?>
                <th>
                  <?php echo __('Updated') ?>
                  <?php if ('updatedUp' == $sf_request->sort): ?>
                    <?php echo link_to(image_tag('up.gif'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
                  <?php else: ?>
                    <?php echo link_to(image_tag('down.gif'), array('sort' => 'updatedUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
                  <?php endif; ?>
                </th>
              <?php endif; ?>
            </tr>
          </thead><tbody>

            <?php foreach ($pager->getResults() as $item): ?>

              <?php $doc = build_i18n_doc($item) ?>
              <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
                <td>
                  <?php echo link_to($doc[$sf_user->getCulture()]['authorizedFormOfName'], array('module' => 'actor', 'slug' => $doc['slug'])) ?>
                </td><td>
                  <?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort || ('lastUpdated' != $sortSetting && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort) ): ?>
                    <?php if (isset($doc['entityTypeId']) && isset($types[$doc['entityTypeId']])): ?>
                      <?php echo $types[$doc['entityTypeId']] ?>
                    <?php else: ?>
                      <?php echo __('N/A') ?>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php echo format_date($item->updatedAt, 'f') ?>
                  <?php endif; ?>
                </td>
              </tr>

            <?php endforeach; ?>

          </tbody>
        </table>

      </div>

      <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

    </div>

  </div>

</div>
