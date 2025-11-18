<?php use_helper('Number'); ?>

<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Treeview settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'treeview'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="general-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#general-collapse" aria-expanded="true" aria-controls="general-collapse">
            <?php echo __('General'); ?>
          </button>
        </h2>
        <div id="general-collapse" class="accordion-collapse collapse show" aria-labelledby="general-heading">
          <div class="accordion-body">
            <div class="row">

              <div class="col-md-6">
                <?php echo render_field($form->type->label(__('Type'))); ?>
              </div>

              <div class="col-md-6">
                <?php echo render_field($form->showBrowseHierarchyPage
                    ->label(__('Show browse hierarchy page'))
                    ->help(__('Determines whether to show a link to the browse hierarchy page on the information objects browse/search pages'))); ?>
              </div>

              <div class="col-md-6">
                <?php echo render_field($form->allowFullWidthTreeviewCollapse
                    ->label(__('Make full width treeview collapsed on description pages'))); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="sidebar-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-collapse" aria-expanded="false" aria-controls="sidebar-collapse">
            <?php echo __('Sidebar'); ?>
          </button>
        </h2>
        <div id="sidebar-collapse" class="accordion-collapse collapse" aria-labelledby="sidebar-heading">
          <div class="accordion-body">
            <?php echo render_field($form->ioSort
                ->label(__('Sort (information object)'))
                ->help(__('Determines whether to sort siblings in the information object treeview control and, if so, what sort criteria to use'))); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="full-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#full-collapse" aria-expanded="false" aria-controls="full-collapse">
            <?php echo __('Full width'); ?>
          </button>
        </h2>
        <div id="full-collapse" class="accordion-collapse collapse" aria-labelledby="full-heading">
          <div class="accordion-body">
            <div class="row">

              <div class="col-md-4">
                <?php echo render_field($form->showIdentifier
                    ->label(__('Show identifier'))); ?>
              </div>

              <div class="col-md-4">
                <?php echo render_field($form->showLevelOfDescription
                    ->label(__('Show level of description'))); ?>
              </div>

              <div class="col-md-4">
                <?php echo render_field($form->showDates
                    ->label(__('Show dates'))); ?>
              </div>

              <div class="col-md-12">
                <?php echo render_field(
                    $form->fullItemsPerPage
                        ->label(__('Items per page'))
                        ->help(__(
                            'Items per page can be a minimum of %1% and a maximum of %2%',
                            [
                                '%1%' => format_number(10),
                                '%2%' => format_number(
                                    sfConfig::get('app_treeview_items_per_page_max', 10000)
                                ),
                            ])),
                    null,
                    ['type' => 'number']
                ); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
