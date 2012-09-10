<?php echo $form->renderFormTag(url_for(array('module' => 'search', 'action' => $action)), array('name' => 'form', 'method' => 'get')) ?>

  <?php echo $form->renderHiddenFields() ?>

  <?php echo get_partial('search/searchFields') ?>

  <p class="headline">
    <button class="btn<?php echo $hasFilters ? ' active' : '' ?>" id="toggle-filters"><?php echo __('Add filter/limit') ?></button>
  </p>

  <div id="filters" class="row-fluid">

    <button class="btn" data-toggle="button"><?php echo __('Filter/Limit') ?></button>

    <div class="row-fluid">

      <div class="span6 left">
        <?php if (sfConfig::get('app_multi_repository')): ?>
          <?php echo $form->repository
            ->label(__('Repository'))
            ->renderRow() ?>
        <?php endif; ?>
      </div>

      <div class="span6 right">
        <?php echo $form->materialType
          ->label(__('General material designation'))
          ->renderRow() ?>
      </div>

    </div>

    <div class="row-fluid">

      <div class="span6 left">
        <?php echo $form->mediaType
          ->label(__('Media type'))
          ->renderRow() ?>
      </div>

      <div class="span6 right">
        <?php echo $form->hasDigitalObject
          ->label(__('Digital object available'))
          ->renderRow() ?>
      </div>

    </div>

    <div class="row-fluid">

      <div class="span6 left">
        <?php echo $form->levelOfDescription->renderRow() ?>
      </div>

      <div class="span6 right">
        <?php echo $form->copyrightStatus
          ->label(__('Copyright status'))
          ->renderRow() ?>
      </div>

    </div>

  </div>

  <div class="actions">
    <button type="submit" class="gray btn-large"><?php echo __('Search') ?></button>
  </div>

</form>
