<?php use_helper('Date') ?>

<div class="row">

  <div class="span9 offset3">
    <h1>
      <?php echo image_tag('/images/icons-large/icon-archival.png') ?>
      <?php echo __('Browse %1% %2%', array(
        '%1%' => $pager->getNbResults(),
        '%2%' => sfConfig::get('app_ui_label_informationobject'))) ?>
    </h1>
  </div>

</div>


<div class="row">

  <div class="span3">

    <div id="facets">

      ...

    </div>

  </div>

  <div class="span9">

    <div id="main-column">

      <div class="section tabs">

        <h2 class="element-invisible"><?php echo __('Information Object Browse Options') ?></h2>

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

      </div>

      <div id="content">

        <section>

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
              <?php foreach ($pager->getResults() as $item): ?>
                <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
                  <td>
                    <?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $item->getPublicationStatus()->status ?></span><?php endif; ?>
                  </td><td>
                    <?php if ('lastUpdated' == $sortSetting): ?>
                      <?php if ('titleUp' == $sf_request->sort || 'titleDown' == $sf_request->sort): ?>
                        <?php echo $item->levelOfDescription ?>
                      <?php else: ?>
                        <?php if (sfConfig::get('app_multi_repository')): ?>
                          <?php if (null !== $repository = $item->getRepository(array('inherit' => true))): ?>
                            <?php echo link_to(render_title($repository), array($repository, 'module' => 'repository')) ?>
                          <?php endif; ?>
                        <?php else: ?>
                          <?php echo $item->levelOfDescription ?>
                        <?php endif; ?>
                      <?php endif; ?>
                    <?php else: ?>
                      <?php if ('updatedUp' == $sf_request->sort || 'updatedDown' == $sf_request->sort): ?>
                        <?php if (sfConfig::get('app_multi_repository')): ?>
                          <?php if (null !== $repository = $item->getRepository(array('inherit' => true))): ?>
                            <?php echo link_to(render_title($repository), array($repository, 'module' => 'repository')) ?>
                          <?php endif; ?>
                        <?php else: ?>
                         <?php echo $item->levelOfDescription ?>
                        <?php endif; ?>
                      <?php else: ?>
                        <?php echo $item->levelOfDescription ?>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td><td>
                    <?php if ('titleDown' == $sf_request->sort || 'titleUp' == $sf_request->sort): ?>
                      <?php if (sfConfig::get('app_multi_repository')): ?>
                        <?php if (null !== $repository = $item->getRepository(array('inherit' => true))): ?>
                          <?php echo link_to(render_title($repository), array($repository, 'module' => 'repository')) ?>
                        <?php endif; ?>
                      <?php else: ?>
                        <ul>
                          <?php foreach ($item->getCreators(array('inherit' => true)) as $creator): ?>
                            <li><?php echo link_to(render_title($creator), array($creator, 'module' => 'actor')) ?></li>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                    <?php else: ?>
                      <?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort || 'lastUpdated' == $sortSetting): ?>
                        <?php echo format_date($item->updatedAt, 'f') ?>
                      <?php else: ?>
                        <?php if (sfConfig::get('app_multi_repository')): ?>
                          <?php if (null !== $repository = $item->getRepository(array('inherit' => true))): ?>
                            <?php echo link_to(render_title($repository), array($repository, 'module' => 'repository')) ?>
                          <?php endif; ?>
                        <?php else: ?>
                          <ul>
                            <?php foreach ($item->getCreators(array('inherit' => true)) as $creator): ?>
                              <li><?php echo link_to(render_title($creator), array($creator, 'module' => 'actor')) ?></li>
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

        </section>

        <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

      </div>

    </div>

  </div>

</div>
