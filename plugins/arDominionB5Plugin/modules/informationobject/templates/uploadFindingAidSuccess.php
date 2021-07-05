<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($resource); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'uploadFindingAid'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion" id="clipboard-load">
      <div class="accordion-item">
        <h2 class="accordion-header" id="load-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#load-collapse" aria-expanded="true" aria-controls="load-collapse">
            <?php echo __('Upload finding aid'); ?>
          </button>
        </h2>
        <div id="load-collapse" class="accordion-collapse collapse show" aria-labelledby="load-heading" data-bs-parent="#clipboard-load">
          <div class="accordion-body">
            <?php if (isset($errorMessage)) { ?>
              <div class="messages error">
                <ul>
                  <li><?php echo $errorMessage; ?></li>
                </ul>
              </div>
            <?php } ?>

            <?php echo $form->file->label(__('%1% file', ['%1%' => strtoupper($format)]))->renderRow(); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Upload'); ?>"/></li>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'c-btn']); ?></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
