<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Move %1%', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <div class="row">
    <div class="inline-search span6" role="search" aria-label="<?php echo __(sfConfig::get('app_ui_label_informationobject')); ?>">
      <form action="<?php echo url_for([$resource, 'module' => 'default', 'action' => 'move']); ?>">
        <div class="input-append">
          <?php if (isset($sf_request->query)) { ?>
            <input type="text" aria-label="<?php echo __('Search title or identifier'); ?>" name="query" value="<?php echo $sf_request->query; ?>" placeholder="<?php echo __('Search title or identifier'); ?>" />
            <a class="btn" href="<?php echo url_for([$resource, 'module' => 'default', 'action' => 'move']); ?>" aria-label=<?php echo __('Reset search'); ?>>
              <i aria-hidden="true" class="fa fa-undo"></i>
            </a>
          <?php } else { ?>
            <input type="text" name="query" aria-label="<?php echo __('Search title or identifier'); ?>" placeholder="<?php echo __('Search title or identifier'); ?>" />
          <?php } ?>
          <div class="btn-group">
            <button class="btn" type="submit" aria-label=<?php echo __('Search'); ?>>
              <i aria-hidden="true" class="fa fa-search"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <?php foreach ($parent->ancestors as $item) { ?>
        <?php if (isset($item->parent)) { ?>
          <li class="breadcrumb-item"><?php echo link_to(render_title($item), [$resource, 'module' => 'default', 'action' => 'move', 'parent' => $item->slug]); ?></li>
        <?php } ?>
      <?php } ?>
      <?php if (isset($parent->parent)) { ?>
        <li class="breadcrumb-item active" aria-current="page"><?php echo render_title($parent); ?></li>
      <?php } ?>
    </ol>
  </nav>

<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php if (count($results)) { ?>
    <div class="table-responsive mb-3">
      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            <th><?php echo __('Identifier'); ?></th>
            <th><?php echo __('Title'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $item) { ?>
            <tr>
              <td width="15%">
                <?php echo render_value_inline($item->identifier); ?>
              </td>
              <td width="85%">
                <?php echo link_to_if($resource->lft > $item->lft || $resource->rgt < $item->rgt, render_title($item), [$resource, 'module' => 'default', 'action' => 'move', 'parent' => $item->slug]); ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'default', 'action' => 'move'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <ul class="actions nav gap-2">
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Move here'); ?>"></li>
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    </ul>

  </form>
<?php end_slot(); ?>
