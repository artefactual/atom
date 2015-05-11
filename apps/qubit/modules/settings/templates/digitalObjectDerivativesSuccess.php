<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_partial('settings/menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Digital object derivatives') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'digitalObjectDerivatives'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Digital object derivatives settings') ?></legend>

        <?php echo $form->pdfPageNumber
          ->label(__('PDF page number for image derivative'))
          ->help(__('If the page number does not exist, the derivative will be generated from the previous closest one.'))
          ->renderRow() ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
