<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

<?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>
<h1>
  <?php echo __('Diacritics settings'); ?>
</h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

<div class="alert alert-info">
  <p>
    <?php echo __('Please rebuild the search index after uploading diacritics mappings.'); ?>
  </p>
  <pre>$ php symfony search:populate</pre>
</div>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'diacritics'])); ?>

<?php echo $form->renderHiddenFields(); ?>

<div class="accordion mb-3">
  <div class="accordion-item">
    <h2 class="accordion-header" id="diacritics-heading">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#diacritics-collapse"
        aria-expanded="true" aria-controls="diacritics-collapse">
        <?php echo __('Diacritics Settings'); ?>
      </button>
    </h2>
    <div id="diacritics-collapse" class="accordion-collapse collapse show" aria-labelledby="diacritics-heading">
      <div class="accordion-body">
        <?php echo render_field($form->diacritics->label(__('Diacritics'))); ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="mappings-heading">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#mappings-collapse"
        aria-expanded="false" aria-controls="mappings-collapse">
        <?php echo __('CSV Mapping YAML'); ?>
      </button>
    </h2>

    <div id="mappings-collapse" class="accordion-collapse collapse show" aria-labelledby="sending-heading">

      <div class="alert alert-info m-3 mb-0">
        <p>
          <?php echo __('Example CSV:'); ?>
        </p>
        <pre>type: mapping<br/>mappings:<br/>  - À => A<br/>  - Á => A</pre>
      </div>

      <div class="accordion-body">
        <?php echo render_field($form->mappings->label(__('Mappings YAML'))); ?>
      </div>
    </div>
  </div>
</div>

<section class="actions">
  <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>" />
</section>

</form>

<?php end_slot(); ?>
