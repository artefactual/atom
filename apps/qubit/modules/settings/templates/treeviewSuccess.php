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

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('General'); ?></legend>

        <div class="row">

          <div class="span4">
            <?php echo $form->type
                ->label(__('Type'))
                ->renderRow(); ?>
          </div>

          <div class="span4">
            <?php echo $form->showBrowseHierarchyPage
                ->label(__('Show browse hierarchy page'))
                ->help(__('Determines whether to show a link to the browse hierarchy page on the information objects browse/search pages'))
                ->renderRow(); ?>
           </div>

        </div>

        <p>
          <?php echo $form->allowFullWidthTreeviewCollapse
              ->label(__('Make full width treeview collapsible on description pages'))
              ->renderRow(); ?>
        </p>

      </fieldset>

      <fieldset class="collapsible">

        <legend><?php echo __('Sidebar'); ?></legend>

        <?php echo $form->ioSort
            ->label(__('Sort (information object)'))
            ->help(__('Determines whether to sort siblings in the information object treeview control and, if so, what sort criteria to use'))
            ->renderRow(); ?>

      </fieldset>

      <fieldset class="collapsible">

        <legend><?php echo __('Full width'); ?></legend>

        <div class="row">

          <div class="span3">
            <?php echo $form->showIdentifier
                ->label(__('Show identifier'))
                ->renderRow(); ?>
          </div>

          <div class="span3">
            <?php echo $form->showLevelOfDescription
                ->label(__('Show level of description'))
                ->renderRow(); ?>
          </div>

          <div class="span2">
            <?php echo $form->showDates
                ->label(__('Show dates'))
                ->renderRow(); ?>
          </div>

        </div>

        <p>
            <?php echo $form->fullItemsPerPage
                ->label(__('Items per page'))
                ->help(
                  __('Items per page can be a minimum of %1% and a maximum of %2%',
                    [
                        '%1%' => format_number(10),
                        '%2%' => format_number(
                          sfConfig::get('app_treeview_items_per_page_max', 10000)
                        ),
                    ]
                  )
                )
                ->renderRow(); ?>
        </p>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
