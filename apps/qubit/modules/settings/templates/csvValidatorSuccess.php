<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('CSV Validator'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'csvValidator'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('CSV Validator settings'); ?></legend>

        <?php echo $form->csv_validator_default_import_behaviour
            ->label(__('CSV Validator default behaviour when CSV Import is run'))
            ->renderRow(); ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
