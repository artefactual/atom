<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Identifier-related'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div class="alert alert-info">
    <p><?php echo __('Please clear the application cache and rebuild the search index if you are changing the reference code separator setting.'); ?></p>
    <pre>$ php symfony cc</pre>
    <pre>$ php symfony search:populate</pre>
  </div>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'identifier'])); ?>
  
    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Identifier settings'); ?></legend>

        <?php echo $form->accession_mask_enabled
            ->label(__('Accession mask enabled'))
            ->renderRow(); ?>

        <?php echo $form->accession_mask
            ->label(__('Accession mask'))
            ->renderRow(); ?>

        <?php echo $form->accession_counter
            ->label(__('Accession counter'))
            ->renderRow(); ?>

        <?php echo $form->identifier_mask_enabled
            ->label(__('Identifier mask enabled'))
            ->renderRow(); ?>

        <?php echo $form->identifier_mask
            ->label(__('Identifier mask'))
            ->renderRow(); ?>

        <?php echo $form->identifier_counter
            ->label(__('Identifier counter'))
            ->renderRow(); ?>

        <?php echo $form->separator_character
            ->label(__('Reference code separator'))
            ->renderRow(); ?>

        <?php echo $form->inherit_code_informationobject
            ->label(__('Inherit reference code (information object)'))
            ->renderRow(); ?>

        <?php echo $form->inherit_code_dc_xml
            ->label(__('Inherit reference code (DC XML)'))
            ->renderRow(); ?>

        <?php echo $form->prevent_duplicate_actor_identifiers
            ->label(__(
              '%1% identifiers: prevent entry/import of duplicates',
              ['%1%' => sfConfig::get('app_ui_label_actor')]
            ))
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
