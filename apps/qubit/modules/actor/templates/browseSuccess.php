<?php use_helper('Date') ?>

<div class="section tabs">

  <h2 class="element-invisible"><?php echo __('Actor browse options') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <?php if ('lastUpdated' == $sortSetting): ?>
        <li<?php if ('nameDown' != $sf_request->sort && 'nameUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
        <li<?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <?php else: ?>
        <li<?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
        <li<?php if ('updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <?php endif; ?>
    </ul>
  </div>

</div>

<h1><?php echo __('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_actor'))) ?></h1>

<table class="sticky-enabled">
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
      </th><th>
        <?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort || ('lastUpdated' != $sortSetting && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort)): ?>
          <?php echo __('Type') ?>
          <?php if ('typeDown' == $sf_request->sort): ?>
            <?php echo link_to(image_tag('up.gif'), array('sort' => 'typeUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php elseif ('typeUp' == $sf_request->sort): ?>
            <?php echo link_to(image_tag('down.gif'), array('sort' => 'typeDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php endif; ?>
        <?php else: ?>
          <?php echo __('Updated') ?>
          <?php if ('updatedUp' == $sf_request->sort): ?>
            <?php echo link_to(image_tag('up.gif'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php else: ?>
            <?php echo link_to(image_tag('down.gif'), array('sort' => 'updatedUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
          <?php endif; ?>
        <?php endif; ?>
      </th><th>
        <?php echo __('Dates') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?>
        </td><td>
          <?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort || ('lastUpdated' != $sortSetting && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort) ): ?>
            <?php echo $item->entityType ?>
          <?php else: ?>
            <?php echo format_date($item->updatedAt, 'f') ?>
          <?php endif; ?>
        </td><td>
          <?php echo $item->datesOfExistence ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<div class="search">
  <form action="<?php echo url_for(array('module' => 'actor', 'action' => 'list')) ?>">
    <input name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>"/>
    <input class="form-submit" type="submit" value="<?php echo __('Search %1%', array('%1%' => sfConfig::get('app_ui_label_actor'))) ?>"/>
  </form>
</div>
