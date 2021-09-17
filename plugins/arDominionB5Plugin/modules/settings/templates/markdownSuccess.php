<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Markdown'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div class="alert alert-info">
    <p><?php echo __('Please rebuild the search index if you are enabling/disabling Markdown support.'); ?></p>
    <pre>$ php symfony search:populate</pre>
  </div>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'markdown'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="markdown-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#markdown-collapse" aria-expanded="true" aria-controls="markdown-collapse">
            <?php echo __('Markdown settings'); ?>
          </button>
        </h2>
        <div id="markdown-collapse" class="accordion-collapse collapse show" aria-labelledby="markdown-heading">
          <div class="accordion-body">
            <?php echo render_field($form->enabled
                ->label(__('Enable Markdown support'))); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
