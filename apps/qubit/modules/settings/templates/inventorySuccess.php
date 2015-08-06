<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Inventory') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'inventory'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Inventory settings') ?></legend>

        <?php if (!empty($unknownValueDetected)): ?>
          <div class="messages error">
            <?php echo __('Unknown value detected.') ?><br />
          </div>
        <?php endif; ?>

        <?php echo $form->levels
          ->label(__('Levels of description'))
          ->help(__('Select the levels of description to be included in the inventory list. If no levels are selected, the inventory list link will not be displayed. You can use the control (Mac ⌘) and/or shift keys to multi-select values from the Levels of description menu.'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <br />
        <?php $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) ?>
        <?php echo link_to(__('Review the current terms in the Levels of description taxonomy.'), array($taxonomy, 'module' => 'taxonomy')) ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
