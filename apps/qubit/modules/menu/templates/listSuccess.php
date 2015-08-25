<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Site menu list') ?>
    <span class="sub"><?php echo __('Hierarchical list of menus for the site, first column') ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <table class="table table-bordered sticky-enabled">
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
        <tr>
          <td<?php if (QubitMenu::ROOT_ID == $item['parentId']): ?> style="font-weight: bold"<?php endif; ?>>

            <?php echo str_repeat('&nbsp;&nbsp;', ($item['depth'] - 1)) ?>

            <?php if (isset($item['prev'])): ?>
              <?php echo link_to(image_tag('up.gif', array('alt' => __('Move up'))), array('module' => 'menu', 'action' => 'list', 'move' => $item['id'], 'before' => $item['prev']), array('title' => __('Move item up in list'))) ?>
            <?php else: ?>
              &nbsp;&nbsp;
            <?php endif; ?>

            <?php if (isset($item['next'])): ?>
              <?php echo link_to(image_tag('down.gif', array('alt' => __('Move down'))), array('module' => 'menu', 'action' => 'list', 'move' => $item['id'], 'after' => $item['next']), array('title' => __('Move item down in list'))) ?>
            <?php else: ?>
              &nbsp;&nbsp;
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

<?php end_slot() ?>

<?php slot('after-content') ?>
  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Add new'), array('module' => 'menu', 'action' => 'add'), array('class' => 'c-btn c-btn-submit')) ?></li>
    </ul>
  </section>
<?php end_slot() ?>
