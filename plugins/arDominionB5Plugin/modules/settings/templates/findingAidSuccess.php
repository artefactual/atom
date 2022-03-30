<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Finding Aid settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $findingAidForm->renderGlobalErrors(); ?>

  <?php echo $findingAidForm->renderFormTag(url_for(['module' => 'settings', 'action' => 'findingAid'])); ?>

    <?php echo $findingAidForm->renderHiddenFields(); ?>

    <div class="accordion mb-3" id="finding-aid-settings">
      <div class="accordion-item">
        <h2 class="accordion-header" id="finding-aid-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#finding-aid-collapse" aria-expanded="true" aria-controls="finding-aid-collapse">
            <?php echo __('Finding Aid settings'); ?>
          </button>
        </h2>
        <div id="finding-aid-collapse" class="accordion-collapse collapse show" aria-labelledby="finding-aid-heading">
          <div class="accordion-body">
            <?php echo render_field($findingAidForm->finding_aids_enabled); ?>

            <?php echo render_field($findingAidForm->finding_aid_format); ?>

            <?php echo render_field($findingAidForm->finding_aid_model); ?>

            <?php echo render_field($findingAidForm->public_finding_aid); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
