<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Site menu list'); ?>
    <span class="sub"><?php echo __('Hierarchical list of menus for the site, first column'); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name'); ?>
        </th><th>
          <?php echo __('Label'); ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($menuTree as $item) { ?>
        <tr>
          <td<?php if (QubitMenu::ROOT_ID == $item['parentId']) { ?> style="font-weight: bold"<?php } ?>>

            <?php echo str_repeat('&nbsp;&nbsp;', $item['depth'] - 1); ?>

            <?php if (isset($item['prev'])) { ?>
              <?php echo link_to(image_tag('up.gif', ['alt' => __('Move up')]), ['module' => 'menu', 'action' => 'list', 'move' => $item['id'], 'before' => $item['prev']], ['title' => __('Move item up in list')]); ?>
            <?php } else { ?>
              &nbsp;&nbsp;
            <?php } ?>

            <?php if (isset($item['next'])) { ?>
              <?php echo link_to(image_tag('down.gif', ['alt' => __('Move down')]), ['module' => 'menu', 'action' => 'list', 'move' => $item['id'], 'after' => $item['next']], ['title' => __('Move item down in list')]); ?>
            <?php } else { ?>
              &nbsp;&nbsp;
            <?php } ?>

            <?php if ($item['protected']) { ?>
              <?php echo link_to($item['name'], [QubitMenu::getById($item['id']), 'module' => 'menu', 'action' => 'edit'], ['class' => 'readOnly', 'title' => __('Edit menu')]); ?>
            <?php } else { ?>
              <?php echo link_to($item['name'], [QubitMenu::getById($item['id']), 'module' => 'menu', 'action' => 'edit'], ['title' => __('Edit menu')]); ?>
            <?php } ?>

          </td><td>
            <?php echo $item['label']; ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Add new'), ['module' => 'menu', 'action' => 'add'], ['class' => 'c-btn c-btn-submit']); ?></li>
    </ul>
  </section>
<?php end_slot(); ?>
