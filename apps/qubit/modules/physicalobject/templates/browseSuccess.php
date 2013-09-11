<?php use_helper('Date') ?>

<h1><?php echo __('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_physicalobject'))) ?></h1>

<table class="table table-ordered sticky-enabled">
  <thead>
    <tr>
      <th class="sortable">
        <?php echo link_to(__('Name'), array('sort' => ('nameUp' == $sf_request->sort) ? 'nameDown' : 'nameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'), 'class' => 'sortable')) ?>
        <?php if ('nameUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif') ?>
        <?php elseif ('nameDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif') ?>
        <?php endif; ?>
      </th><th class="sortable">
        <?php echo link_to(__('Location'), array('sort' => ('locationUp' == $sf_request->sort) ? 'locationDown' : 'locationUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'), 'class' => 'sortable')) ?>
        <?php if ('locationUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif') ?>
        <?php elseif ('locationDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif') ?>
        <?php endif; ?>
      </th><th>
        <?php echo __('Type') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <?php echo link_to(render_title($item), array($item, 'module' => 'physicalobject')) ?>
        </td>
        <td>
          <?php echo $item->location ?>
        </td>
        <td>
          <?php echo $item->type?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>
