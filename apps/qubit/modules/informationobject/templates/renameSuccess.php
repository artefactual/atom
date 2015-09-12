<?php decorate_with('layout_1col.php') ?>

<?php $sf_response->addJavaScript('renameModal'); ?>
<?php $sf_response->addJavaScript('description'); ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Rename') ?>: <?php echo $resource->title ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

<div id="content" class="yui-panel">

  <form id="renameForm" action="<?php echo url_for(array('module' => 'informationobject', 'action' => 'rename', 'slug' => $resource->slug)) ?>" method="POST">

    <div class="alert"><?php echo __('Use this interface to update the description title, slug (permalink), and/or digital object filename.') ?></div>

    <div>
      <div style="float:right"><input id="rename_enable_title" type="checkbox" checked="checked" /> <?php echo __('Update title') ?></div>
      <label><?php echo $form->title->label ?></label>
      <?php echo $form->title ?>
      <div class="description"><?php echo $form->title->help ?></div>
      <p><?php echo __('Original title') ?>: <em><?php echo $resource->title ?></em></p>
    </div>

    <div>
      <div style="float:right"><input id="rename_enable_slug" type="checkbox" checked="checked" /> <?php echo __('Update slug') ?></div>
      <label><?php echo $form->slug->label ?></label>
      <?php echo $form->slug ?>
      <div class="description"><?php echo $form->slug->help ?></div>
      <p><?php echo __('Original slug') ?>: <em><?php echo $resource->slug ?></em></p>
    </div>

    <?php if (count($resource->digitalObjects) > 0): ?>
    <div>
      <div style="float:right"><input id="rename_enable_filename" type="checkbox" /> <?php echo __('Update filename') ?></div>
      <label><?php echo $form->filename->label ?></label>
      <?php echo $form->filename ?>
      <div class="description"><?php echo $form->filename->help ?></div>
      <p><?php echo __('Original filename') ?>: <em><?php echo $resource->digitalObjects[0]->name ?></em></p>
    </div>
    <?php endif; ?>

    <section class="actions">
      <ul>
        <li><a href="#" id="renameFormSubmit" class="c-btn c-btn-submit"><?php echo __('Update') ?></a></li>
        <li><?php echo link_to(__('Cancel'), array('module' => 'informationobject', 'action' => 'browse'), array('class' => 'c-btn c-btn-delete')) ?></li>
      </ul>
    </section>

  </form>

</div>

<?php end_slot() ?>
