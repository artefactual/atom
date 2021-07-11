<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo render_title($resource); ?>
    <span class="sub"><?php echo __('Link %1%', ['%1%' => sfConfig::get('app_ui_label_physicalobject')]); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => $sf_context->getModuleName(), 'action' => 'editPhysicalObjects'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <?php if (0 < count($relations)) { ?>
      <div id="content" class="border-bottom-0 rounded-0 rounded-top">
        <table style="width: 98%;">
          <thead>
            <tr>
              <th colspan="2" style="width: 90%;">
                <?php echo __('Containers'); ?>
              </th><th style="width: 5%;">
              </th>
            </tr>
          </thead><tbody>
            <?php foreach ($relations as $item) { ?>
              <tr class="related_obj_<?php echo $item->id; ?>">
                <td style="width: 90%"><div class="animateNicely">
                  <?php echo $item->subject->getLabel(); ?>
                </div></td><td style="width: 20px;"><div class="animateNicely">
                  <?php echo link_to(image_tag('pencil', ['style' => 'align: top', 'alt' => __('Edit')]), [$item->subject, 'module' => 'physicalobject', 'action' => 'edit']); ?>
                </div></td><td style="width: 20px;"><div class="animateNicely">
                  <input class="multiDelete" name="delete_relations[]" type="checkbox" value="<?php echo url_for([$item, 'module' => 'relation']); ?>"/>
                </div></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } ?>

    <div class="accordion">
      <div class="accordion-item<?php echo count($relations) ? ' rounded-0' : ''; ?>">
        <h2 class="accordion-header" id="add-heading">
          <button class="accordion-button<?php echo count($relations) ? ' rounded-0' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#add-collapse" aria-expanded="true" aria-controls="add-collapse">
            <?php echo __('Add container links (duplicate links will be ignored)'); ?>
          </button>
        </h2>
        <div id="add-collapse" class="accordion-collapse collapse show" aria-labelledby="add-heading">
          <div class="accordion-body">
            <div class="form-item">
              <?php echo $form->containers->renderLabel(); ?>
              <?php echo $form->containers->render(['class' => 'form-autocomplete', 'data-autocomplete-delay' => 0.3]); ?>
              <input class="add" type="hidden" data-link-existing="false" value="<?php echo url_for([$resource, 'module' => $sf_context->getModuleName(), 'action' => 'editPhysicalObjects']); ?> #name"/>
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'physicalobject', 'action' => 'autocomplete']); ?>"/>
            </div>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="create-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#create-collapse" aria-expanded="false" aria-controls="create-collapse">
            <?php echo __('Or, create a new container'); ?>
          </button>
        </h2>
        <div id="create-collapse" class="accordion-collapse collapse" aria-labelledby="create-heading">
          <div class="accordion-body">
            <div class="form-item">
              <?php echo $form->name->renderRow(); ?>
              <?php echo $form->location->renderRow(); ?>
              <?php echo $form->type->renderRow(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => $sf_context->getModuleName()], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
