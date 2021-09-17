<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Move %1%', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <div class="d-inline-block mb-3">
    <?php echo get_component('search', 'inlineSearch', [
        'label' => __('Search title or identifier'),
        'landmarkLabel' => sfConfig::get('app_ui_label_informationobject'),
        'route' => url_for([$resource, 'module' => 'default', 'action' => 'move']),
    ]); ?>
  </div>

  <?php if (0 < count($parent->ancestors)) { ?>
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
  <?php } ?>
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

    <ul class="actions mb-3 nav gap-2">
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Move here'); ?>"></li>
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    </ul>

  </form>
<?php end_slot(); ?>
