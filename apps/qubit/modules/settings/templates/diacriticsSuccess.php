<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

<?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

<h1>
  <?php echo __('Diacritics'); ?>
</h1>

<?php end_slot(); ?>

<?php slot('content'); ?>
<div class="alert alert-info">
  <p>
    <?php echo __('Please rebuild the search index after uploading diacritics mappings.'); ?>
  </p>
  <pre>$ php symfony search:populate</pre>
</div>

<div class="alert alert-info">
  <p>
    <?php echo __('Example CSV:'); ?>
  </p>
  <pre>type: mapping<br/>mappings:<br/>  - À => A<br/>  - Á => A</pre>
</div>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'diacritics'])); ?>

<?php echo $form->renderHiddenFields(); ?>

<div id="content">

  <fieldset class="collapsible">
    <legend>
      <?php echo __('Diacritics settings'); ?>
    </legend>

    <?php echo $form->diacritics->label(__('Diacritics'))->renderRow(); ?>
  </fieldset>

  <fieldset class="collapsible">
    <legend>
      <?php echo __('CSV Mapping YAML'); ?>
    </legend>

    <?php echo $form->mappings->label(__('Mappings YAML'))->renderRow(); ?>
  </fieldset>

</div>

<section class="actions">
  <ul>
    <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>" /></li>
  </ul>
</section>

</form>

<?php end_slot(); ?>