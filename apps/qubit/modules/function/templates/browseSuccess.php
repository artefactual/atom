<?php decorate_with('layout_1col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo image_tag('/images/icons-large/icon-functions.png') ?>
    <?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?>
    <span class="sub"><?php echo sfConfig::get('app_ui_label_function') ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <ul class="nav nav-tabs">

    <?php if ('lastUpdated' == $sortSetting): ?>
      <li<?php if ('nameDown' != $sf_request->sort && 'nameUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <li<?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
    <?php else: ?>
      <li<?php if ('updatedDown' == $sf_request->sort || 'updatedUp' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Recent changes'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
      <li<?php if ('updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
    <?php endif; ?>

    <li class="search">
      <form action="<?php echo url_for(array('module' => 'function', 'action' => 'list')) ?>">
        <input name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>"/>
        <input class="form-submit" type="submit" value="<?php echo __('Search function') ?>"/>
      </form>
    </li>

  </ul>
<?php end_slot() ?>

<?php slot('content') ?>
  <table class="table table-bordered">
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
        <?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort || ('lastUpdated' != $sortSetting && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort)): ?>
          <th>
            <?php echo __('Type') ?>
          </th>
        <?php else: ?>
          <th>
            <?php echo __('Updated') ?>
            <?php if ('lastUpdated' == $sortSetting): ?>
              <?php if ('updatedUp' == $sf_request->sort): ?>
                <?php echo link_to(image_tag('up.gif'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
              <?php else: ?>
                <?php echo link_to(image_tag('down.gif'), array('sort' => 'updatedUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
              <?php endif; ?>
            <?php else: ?>
              <?php if ('updatedUp' == $sf_request->sort): ?>
                <?php echo link_to(image_tag('up.gif'), array('sort' => 'updatedDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
              <?php elseif ('updatedDown' == $sf_request->sort): ?>
                <?php echo link_to(image_tag('down.gif'), array('sort' => 'updatedUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
              <?php endif; ?>
            <?php endif; ?>
          </th>
        <?php endif; ?>
      </tr>
    </thead><tbody>

      <?php foreach ($pager->getResults() as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
          <td>
            <?php echo link_to(render_title($item), array($item, 'module' => 'function')) ?>
          </td>
          <?php if ('nameDown' == $sf_request->sort || 'nameUp' == $sf_request->sort || ('lastUpdated' != $sortSetting && 'updatedDown' != $sf_request->sort && 'updatedUp' != $sf_request->sort) ): ?>
            <td>
              <?php echo $item->type ?>
            </td>
          <?php else: ?>
            <td>
              <?php echo format_date($item->updatedAt, 'f') ?>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>

    </tbody>
  </table>
<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
