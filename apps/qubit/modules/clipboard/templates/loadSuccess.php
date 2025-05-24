<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>

  <div class="alert alert-info">
    <?php echo __('Enter the ID of the saved clipboard you would like to load.'); ?>
    <?php echo __('In the "Action" selector, indicate whether you want to <strong>merge</strong> the saved clipboard with the entries on the current clipboard or <strong>replace</strong> (overwrite) the current clipboard with the saved one.'); ?>
  </div>

  <h1><?php echo __('Load clipboard'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'clipboard', 'action' => 'load']), ['id' => 'clipboard-load-form']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend class="sr-only"><?php echo __('Load options'); ?></legend>

        <div class="fieldset-wrapper">
          <?php echo $form->clipboardPassword->label(__('Clipboard ID'))->renderRow(); ?>
        </div>

        <div class="fieldset-wrapper">
          <?php echo $form->mode->label(__('Action'))->renderRow(); ?>
        </div>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" name="load" type="submit" value="<?php echo __('Load'); ?>"/></li>
        <li><input class="c-btn c-btn-submit" name="loadView" type="submit" value="<?php echo __('Load and view'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
