<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('%1% derivatives', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))) ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'digitalObjectDerivatives'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('%1% derivatives settings', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))) ?></legend>

        <?php if ($pdfinfoAvailable): ?>
          <?php echo $form->pdfPageNumber
            ->label(__('PDF page number for image derivative'))
            ->help(__('If the page number does not exist, the derivative will be generated from the previous closest one.'))
            ->renderRow() ?>
        <?php else: ?>
          <div class="messages error">
            <?php echo __('The pdfinfo tool is required to use this functionality. Please contact your system administrator.') ?>
          </div>
        <?php endif; ?><br />

        <?php echo $form->refImageMaxWidth
          ->label(__('Maximum image width (pixels)'))
          ->help(__('The maximum width for derived reference images.'))
          ->renderRow() ?>

      </fieldset>

    </div>

    <?php if ($pdfinfoAvailable): ?>
      <section class="actions">
        <ul>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        </ul>
      </section>
    <?php endif; ?>

  </form>

<?php end_slot() ?>
