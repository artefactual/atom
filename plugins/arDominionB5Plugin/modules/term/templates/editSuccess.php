<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Term %1%', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'term', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'term', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="elements-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#elements-collapse" aria-expanded="false" aria-controls="elements-collapse">
            <?php echo __('Elements area'); ?>
          </button>
        </h2>
        <div id="elements-collapse" class="accordion-collapse collapse" aria-labelledby="elements-heading">
          <div class="accordion-body">
            <div class="form-item">
              <?php echo $form->taxonomy->renderLabel(); ?>
              <?php echo $form->taxonomy->renderError(); ?>
              <?php echo $form->taxonomy->render(['class' => 'form-autocomplete']); ?>
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'taxonomy', 'action' => 'autocomplete']); ?>"/>
            </div>

            <?php if (QubitTerm::isProtected($resource->id)) { ?>
              <?php echo $form->name->renderRow(['class' => 'readOnly', 'disabled' => 'disabled']); ?>
            <?php } else { ?>
              <?php echo render_field($form->name, $resource); ?>
            <?php } ?>

            <?php echo $form->useFor
                ->label(__('Use for'))
                ->renderRow(); ?>

            <?php echo render_field($form->code, $resource); ?>

            <?php echo $form->scopeNote
                ->label(__('Scope note(s)'))
                ->renderRow(); ?>

            <?php echo $form->sourceNote
                ->label(__('Source note(s)'))
                ->renderRow(); ?>

            <?php echo $form->displayNote
                ->label(__('Display note(s)'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="relationships-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#relationships-collapse" aria-expanded="false" aria-controls="relationships-collapse">
            <?php echo __('Relationships'); ?>
          </button>
        </h2>
        <div id="relationships-collapse" class="accordion-collapse collapse" aria-labelledby="relationships-heading">
          <div class="accordion-body">
            <div class="form-item">
              <?php echo $form->parent
                  ->label(__('Broad term'))
                  ->renderLabel(); ?>
              <?php echo $form->parent->render(['class' => 'form-autocomplete']); ?>
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete']); ?>"/>
            </div>

            <div class="form-item">
              <?php echo $form->relatedTerms
                  ->label(__('Related term(s)'))
                  ->renderLabel(); ?>
              <?php echo $form->relatedTerms->render(['class' => 'form-autocomplete']); ?>
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete']); ?>"/>
            </div>

            <div class="row">

              <div class="span9">
                <?php echo $form->converseTerm
                    ->label(__('Converse term'))
                    ->renderLabel(); ?>
                <?php echo $form->converseTerm->render(['class' => 'form-autocomplete']); ?>
                <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::ROOT_ID), 'module' => 'taxonomy'])]); ?> #name"/>
                <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete']); ?>"/>
              </div>

              <div class="span2 field-lowering pull-right">
                <?php echo $form->selfReciprocal->label(__('Self-reciprocal'))->renderRow(); ?>
              </div>

            </div>

            <?php echo $form->narrowTerms
                ->label(__('Add new narrow terms'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions nav gap-2">
      <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'term'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
      <?php } else { ?>
        <?php if (isset($resource->taxonomy)) { ?>
          <li><?php echo link_to(__('Cancel'), [$resource->taxonomy, 'module' => 'taxonomy'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <?php } elseif (isset($sf_request->taxonomy)) { ?>
          <li><?php echo link_to(__('Cancel'), !empty($parent) ? $parent : $sf_request->taxonomy, ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <?php } else { ?>
          <li><?php echo link_to(__('Cancel'), ['module' => 'taxonomy', 'action' => 'list'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <?php } ?>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
      <?php } ?>
    </ul>

  </form>

<?php end_slot(); ?>
