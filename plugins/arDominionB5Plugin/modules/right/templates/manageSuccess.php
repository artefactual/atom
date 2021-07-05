<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo render_title($resource); ?>
    <span class="sub"><?php __('Manage rights inheritance'); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <form method="post">

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="inheritance-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#inheritance-collapse" aria-expanded="true" aria-controls="inheritance-collapse">
            <?php echo __('Inheritance options'); ?>
          </button>
        </h2>
        <div id="inheritance-collapse" class="accordion-collapse collapse show" aria-labelledby="inheritance-heading">
          <div class="accordion-body">
            <div class="well">
              <?php echo $form->all_or_digital_only
                  ->label(__('All descendants or just digital objects'))
                  ->renderRow(); ?>
            </div>

            <div class="well">
              <?php echo $form->overwrite_or_combine
                  ->help(__('Set if you want to combine the current set of rights with any existing rights, or remove the existing rights and apply these new rights'))
                  ->label(__('Overwrite or combine rights'))
                  ->renderRow(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'c-btn']); ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Apply'); ?>"/></li>
      </ul>
    </section>
  </form>

<?php end_slot(); ?>
