<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <?php if (isset($resource) && 'QubitTerm' == $resource->getClass()) { ?>
    <div class="multiline-header d-flex flex-column mb-3">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php echo __('SKOS import'); ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo render_title($parent); ?>
      </span>
    </div>
  <?php } else { ?>
    <h1><?php echo __('SKOS import'); ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php if ($form->hasGlobalErrors()) { ?>
    <div class="alert alert-danger" role="alert">
      <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
        <?php foreach ($form->getGlobalErrors() as $error) { ?>
          <?php $error = sfOutputEscaper::unescape($error); ?>
          <li><?php echo $error->getMessage(); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <?php if (QubitTerm::ROOT_ID == $parent->id) { ?>
    <?php echo $form->renderFormTag(url_for([$taxonomy, 'module' => 'sfSkosPlugin', 'action' => 'import'])); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for([$parent, 'module' => 'sfSkosPlugin', 'action' => 'import'])); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="import-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#import-collapse" aria-expanded="true" aria-controls="import-collapse">
            <?php echo __('Import options'); ?>
          </button>
        </h2>
        <div id="import-collapse" class="accordion-collapse collapse show" aria-labelledby="import-heading">
          <div class="accordion-body">
            <?php if (isset($resource)) { ?>
              <div class="form-item">
                <?php echo render_field($form->taxonomy); ?>
              </div>
            <?php } else { ?>
              <?php echo render_field(
                  $form->taxonomy,
                  null,
                  [
                      'class' => 'form-autocomplete',
                      'extraInputs' => '<input class="list" type="hidden" value="'
                          .url_for([
                              'module' => 'informationobject',
                              'action' => 'autocomplete',
                          ])
                          .'">',
                  ]
                ); ?>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="select-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#select-collapse" aria-expanded="true" aria-controls="select-collapse">
            <?php echo __('Select source'); ?>
          </button>
        </h2>
        <div id="select-collapse" class="accordion-collapse collapse show" aria-labelledby="select-heading">
          <div class="accordion-body">
            <?php echo render_field($form->file->label(__('Select a file to import'))); ?>

            <?php echo render_field($form->url->label(__('Or a remote resource'))); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Import'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
