<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('User interface label'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $uiLabelForm->renderGlobalErrors(); ?>

  <?php echo $uiLabelForm->renderFormTag(url_for(['module' => 'settings', 'action' => 'interfaceLabel'])); ?>
    
    <?php echo $uiLabelForm->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="interface-label-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#interface-label-collapse" aria-expanded="true" aria-controls="interface-label-collapse">
            <?php echo __('User interface labels'); ?>
          </button>
        </h2>
        <div id="interface-label-collapse" class="accordion-collapse collapse show" aria-labelledby="interface-label-heading">
          <div class="accordion-body">
            <?php foreach ($uiLabelForm->getSettings() as $setting) { ?>
              <?php $name = $setting->getName(); ?>
              <?php echo render_field($uiLabelForm->{$name}->label('<code>'.$name.'</code>'), $setting); ?>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
