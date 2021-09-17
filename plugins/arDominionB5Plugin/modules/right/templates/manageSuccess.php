<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo render_title($resource); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo __('Manage rights inheritance'); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <form method="post">

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="inheritance-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#inheritance-collapse" aria-expanded="true" aria-controls="inheritance-collapse">
            <?php echo __('Inheritance options'); ?>
          </button>
        </h2>
        <div id="inheritance-collapse" class="accordion-collapse collapse show" aria-labelledby="inheritance-heading">
          <div class="accordion-body">
            <div class="well">
              <?php echo render_field($form->all_or_digital_only
                  ->label(__('All descendants or just digital objects'))
              ); ?>
            </div>

            <div class="well">
              <?php echo render_field($form->overwrite_or_combine
                  ->help(__('Set if you want to combine the current set of rights with any existing rights, or remove the existing rights and apply these new rights'))
                  ->label(__('Overwrite or combine rights'))
              ); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Apply'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
