<h1><?php echo __('Site menu list') ?></h1>

<table class="sticky-enabled" summary="<?php echo __('Hierarchical list of menus for the site, first column') ?>">
  <thead>
    <tr>
      <th>
        <?php echo __('Name') ?>
      </th><th>
        <?php echo __('Label') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($menuTree as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td<?php if (QubitMenu::ROOT_ID == $item['parentId']): ?> style="font-weight: bold"<?php endif; ?>>

          <?php echo str_repeat('&nbsp;&nbsp;', ($item['depth'] - 1)) ?>

          <?php if (isset($item['prev'])): ?>
            <?php echo link_to(image_tag('up.gif', array('alt' => __('Move up'))), array('module' => 'menu', 'action' => 'list', 'move' => $item['id'], 'before' => $item['prev']), array('title' => __('Move item up in list'))) ?>
          <?php else: ?>
            <?php echo image_tag('1x1_transparent', array('height' => '5', 'width' => '13')) ?>
          <?php endif; ?>

          <?php if (isset($item['next'])): ?>
            <?php echo link_to(image_tag('down.gif', array('alt' => __('Move down'))), array('module' => 'menu', 'action' => 'list', 'move' => $item['id'], 'after' => $item['next']), array('title' => __('Move item down in list'))) ?>
          <?php else: ?>
            <?php echo image_tag('1x1_transparent', array('height' => '5', 'width'=>'13')) ?>
          <?php endif; ?>

          <?php if ($item['protected']): ?>
            <?php echo link_to($item['name'], array(QubitMenu::getById($item['id']), 'module' => 'menu', 'action' => 'edit'), array('class' => 'readOnly', 'title' => __('Edit menu'))) ?>
          <?php else: ?>
            <?php echo link_to($item['name'], array(QubitMenu::getById($item['id']), 'module' => 'menu', 'action' => 'edit'), array('title' => __('Edit menu'))) ?>
          <?php endif; ?>

        </td><td>
          <?php echo $item['label'] ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <li><?php echo link_to(__('Add new'), array('module' => 'menu', 'action' => 'add')) ?></li>
    </ul>
  </div>

</div>
