<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Site menu list'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo __('Hierarchical list of menus for the site, first column'); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Name'); ?>
          </th><th>
            <?php echo __('Label'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($menuTree as $item) { ?>
	  <tr>
            <td<?php if (QubitMenu::ROOT_ID == $item['parentId']) { ?> class="fw-bold"<?php } ?>>

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
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <section class="actions mb-3">
    <?php echo link_to(__('Add new'), ['module' => 'menu', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?>
  </section>
<?php end_slot(); ?>
