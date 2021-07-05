<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <?php if (isset($resource) && 'QubitTerm' == $resource->getClass()) { ?>
    <div class="multiline-header">
      <h1><?php echo __('SKOS import'); ?></h1>
      <span class="sub"><?php echo render_title($parent); ?></span>
    </div>
  <?php } else { ?>
    <h1><?php echo __('SKOS import'); ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php if ($form->hasGlobalErrors()) { ?>
    <div class="messages error">
      <ul>
        <?php foreach ($form->getGlobalErrors() as $error) { ?>
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

    <div class="accordion" id="skos-import">
      <div class="accordion-item">
        <h2 class="accordion-header" id="import-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#import-collapse" aria-expanded="true" aria-controls="import-collapse">
            <?php echo __('Import options'); ?>
          </button>
        </h2>
        <div id="import-collapse" class="accordion-collapse collapse show" aria-labelledby="import-heading" data-bs-parent="#skos-import">
          <div class="accordion-body">
            <?php if (isset($resource)) { ?>
              <div class="form-item">
                <?php echo $form->taxonomy->renderLabel(); ?>
                <?php echo $form->taxonomy->renderError(); ?>
                <?php echo $form->taxonomy->render(); ?>
                <?php echo render_title($taxonomy); ?>
              </div>
            <?php } else { ?>
              <?php echo $form->taxonomy->renderLabel(); ?>
              <?php echo $form->taxonomy->renderError(); ?>
              <?php echo $form->taxonomy->render(); ?> 
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'taxonomy', 'action' => 'autocomplete']); ?>"/>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="select-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#select-collapse" aria-expanded="false" aria-controls="select-collapse">
            <?php echo __('Select source'); ?>
          </button>
        </h2>
        <div id="select-collapse" class="accordion-collapse collapse" aria-labelledby="select-heading" data-bs-parent="#skos-import">
          <div class="accordion-body">
            <?php echo $form->file
                ->label(__('Select a file to import'))
                ->renderRow(); ?>

            <br /> <!-- Not ideal! -->

            <?php echo $form->url
                ->label(__('Or a remote resource'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
