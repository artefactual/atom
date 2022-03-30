<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php echo get_component('repository', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <h1><?php echo render_title($resource); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'repository', 'action' => 'editTheme'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="style-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#style-collapse" aria-expanded="true" aria-controls="style-collapse">
            <?php echo __('Style'); ?>
          </button>
        </h2>
        <div id="style-collapse" class="accordion-collapse collapse show" aria-labelledby="style-heading">
          <div class="accordion-body">
            <?php echo render_field($form->backgroundColor); ?>

            <?php echo render_field($form->banner); ?>

            <?php echo render_field($form->logo); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="content-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#content-collapse" aria-expanded="false" aria-controls="content-collapse">
            <?php echo __('Page content'); ?>
          </button>
        </h2>
        <div id="content-collapse" class="accordion-collapse collapse" aria-labelledby="content-heading">
          <div class="accordion-body">
            <?php echo render_field($form->htmlSnippet
                ->label(__('Description'))
                ->help(__('Content in this area will appear below an uploaded banner and above the institution\'s description areas. It can be used to offer a summary of the institution\'s mandate, include a tag line or important information, etc. HTML and inline CSS can be used to style the contents.')), $resource, ['class' => 'resizable']); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'repository'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>
<?php end_slot(); ?>
