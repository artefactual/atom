<?php use_helper('Date') ?>

<div class="section tabs">

  <h2 class="element-invisible"><?php echo __('Information Object Browse Options') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <?php if ('lastUpdated' == $sortSetting): ?>
        <li<?php if ('titleDown' != $sf_request->sort && 'titleUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
        <li<?php if ('titleDown' == $sf_request->sort || 'titleUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'titleUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <?php else: ?>
        <li<?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
        <li<?php if ('updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'titleUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <?php endif; ?> 
    </ul>
  </div>

</div>

<h1><?php echo __('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))) ?></h1>

<table class="sticky-enabled">
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

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<div class="search">
  <form action="<?php echo url_for(array('module' => 'search')) ?>">
    <input name="query" value="<?php echo $sf_request->query ?>"/>
    <input class="form-submit" type="submit" value="<?php echo __('Search %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))) ?>"/>
  </form>
</div>
