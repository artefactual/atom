<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Treeview settings') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'treeview'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('General') ?></legend>

        <?php echo $form->type
          ->label(__('Type'))
          ->renderRow() ?>

      </fieldset>

      <fieldset class="collapsible">

        <legend><?php echo __('Sidebar') ?></legend>

        <?php echo $form->ioSort
          ->label(__('Sort (information object)'))
          ->help(__('Determines whether to sort siblings in the information object treeview control and, if so, what sort criteria to use'))
          ->renderRow() ?>

      </fieldset>

      <fieldset class="collapsible">

        <legend><?php echo __('Full width') ?></legend>

        <div class="row">

          <div class="span3">
            <?php echo $form->showIdentifier
              ->label(__('Show identifier'))
              ->renderRow() ?>
          </div>

          <div class="span3">
            <?php echo $form->showLevelOfDescription
              ->label(__('Show level of description'))
              ->renderRow() ?>
          </div>

          <div class="span2">
            <?php echo $form->showDates
              ->label(__('Show dates'))
              ->renderRow() ?>
          </div>

        </div>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
