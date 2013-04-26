<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1>
    <?php if (isset($icon)): ?>
      <?php echo image_tag('/images/icons-large/icon-'.$icon.'.png') ?>
    <?php endif; ?>
    <?php echo __('Browse %1% %2%', array(
      '%1%' => $pager->getNbResults(),
      '%2%' => strtolower(render_title($resource)))) ?>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <ul class="nav nav-tabs">
    <li<?php if ('hitsUp' == $sf_request->sort || 'hitsDown' == $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Results'), array('sort' => 'hitsUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
    <li<?php if ('hitsUp' != $sf_request->sort && 'hitsDown' != $sf_request->sort): ?> class="active"<?php endif; ?>><?php echo link_to(__('Alphabetic'), array('sort' => 'termNameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?></li>
  </ul>
<?php end_slot() ?>

<table class="table table-bordered sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo render_title($resource) ?>
        <?php if ('termNameDown' == $sf_request->sort): ?>
          <?php echo link_to(image_tag('up.gif'), array('sort' => 'termNameUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
        <?php elseif ('termNameUp' == $sf_request->sort || !in_array($sf_request->sort, array('hitsDown', 'hitsUp'))): ?>
          <?php echo link_to(image_tag('down.gif'), array('sort' => 'termNameDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
        <?php endif; ?>
      </th><th>
        <?php echo __('Results') ?>
        <?php if ('hitsDown' == $sf_request->sort): ?>
          <?php echo link_to(image_tag('up.gif'), array('sort' => 'hitsUp') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
        <?php elseif ('hitsUp' == $sf_request->sort): ?>
          <?php echo link_to(image_tag('down.gif'), array('sort' => 'hitsDown') + $sf_request->getParameterHolder()->getAll(), array('title' => __('Sort'))) ?>
        <?php endif; ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($terms as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <?php echo link_to(render_title($item), array($item, 'module' => 'term', 'action' => 'browseTerm')) ?>
        </td><td>
          <?php echo $item->countRelatedInformationObjects() ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
