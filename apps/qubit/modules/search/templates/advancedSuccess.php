<?php decorate_with('layout_2col') ?>

<?php slot('title') ?>

  <?php if ('print' == $sf_request->getParameter('media')): ?>
    <div id="preview-message">
      <?php echo __('Print preview') ?>
      <?php echo link_to('Close', array_diff($sf_request->getParameterHolder()->getAll(), array('media' => 'print'))) ?>
    </div>
  <?php endif; ?>

  <h1>
    <?php echo __('Advanced search') ?>
    <div id="action-icons">
      <?php echo link_to(
        image_tag('printer-icon.png', array('alt' => __('Print'))),
          array_merge($sf_request->getParameterHolder()->getAll(), array('media' => 'print')),
          array('title' => __('Print'))) ?>
    </div>
  </h1>

<?php end_slot() ?>

<?php slot('sidebar') ?>

  <section id="advance-search-filters">

    <h4><?php echo __('Search filters') ?></h4>

    <div class="filter">
      <?php if (sfConfig::get('app_multi_repository')): ?>
        <?php echo $form->repository
          ->label(__('Repository'))
          ->renderRow() ?>
      <?php endif; ?>
    </div>
    <div class="filter">
      <?php echo $form->materialType
        ->label(__('General material designation'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->mediaType
        ->label(__('Media type'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->hasDigitalObject
        ->label(__('Digital object available'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->levelOfDescription->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->copyrightStatus
        ->label(__('Copyright status'))
        ->renderRow() ?>
    </div>

  </section>

<?php end_slot() ?>

<?php echo $form->renderFormTag(url_for(array('module' => 'search', 'action' => 'advanced')), array('name' => 'form', 'method' => 'get')) ?>

  <?php echo $form->renderHiddenFields() ?>

  <?php echo get_partial('search/searchFields') ?>

  <div class="actions">
    <button type="submit" class="gray btn-large"><?php echo __('Search') ?></button>
  </div>

</form>
