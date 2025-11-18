<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

<?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>
<h1>
  <?php echo __('Header customizations'); ?>
</h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'header'])); ?>

<?php echo $form->renderHiddenFields(); ?>

<div class="accordion mb-3">
  <div class="accordion-item">
    <h2 class="accordion-header" id="logo-heading">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#header-collapse"
        aria-expanded="true" aria-controls="header-collapse">
        <?php echo __('Upload logo'); ?>
      </button>
    </h2>

    <div id="logo-collapse" class="accordion-collapse collapse show" aria-labelledby="sending-heading">

      <div class="alert alert-info m-3 mb-0">
        <p><?php echo __('The logo file must be in “Portable Network Graphics” (PNG) format and the maximum height recommendation for a logo is 50px.'); ?></p>
        <p><?php echo __('Note that browser cache may need to be cleared after uploading a new logo.'); ?></p>
      </div>

      <div class="accordion-body">
        <?php echo render_field($form->logo->label(__('Upload logo'))); ?>

        <?php echo render_field($form->restore_logo->label(__('Restore logo'))); ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="favicon-heading">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#header-collapse"
        aria-expanded="true" aria-controls="header-collapse">
        <?php echo __('Upload favicon'); ?>
      </button>
    </h2>

    <div id="favicon-collapse" class="accordion-collapse collapse show" aria-labelledby="sending-heading">

      <div class="alert alert-info m-3 mb-0">
        <p><?php echo __('The favicon file must be in ICO file format.'); ?></p>
        <p><?php echo __('Note that browser cache may need to be cleared after uploading a new favicon.'); ?></p>
      </div>

      <div class="accordion-body">
        <?php echo render_field($form->favicon->label(__('Upload favicon'))); ?>

        <?php echo render_field($form->restore_favicon->label(__('Restore favicon'))); ?>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="background-heading">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#background-collapse" aria-expanded="true" aria-controls="background-collapse">
        <?php echo __('Change header background colour'); ?>
      </button>
    </h2>
    <div id="background-collapse" class="accordion-collapse collapse show" aria-labelledby="background-heading">
      <div class="accordion-body">
        <?php echo render_field($form->header_background_colour); ?>
      </div>
    </div>
  </div>
</div>

<section class="actions">
  <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>" />
</section>

</form>

<?php end_slot(); ?>
